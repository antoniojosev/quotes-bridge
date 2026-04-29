#!/bin/sh
set -e

PACKAGE_DIR=/package
HOST_DIR=/var/www/host

echo "==> Quotes Bridge bootstrap"

# Allow git to operate on the bind-mounted package even though it's owned by
# the host user (the container runs as root).
git config --global --add safe.directory /package
git config --global --add safe.directory /var/www/host

# 1) Create fresh Laravel host app if missing
if [ ! -f "${HOST_DIR}/composer.json" ]; then
    echo "==> Creating fresh Laravel app at ${HOST_DIR}"
    composer create-project --prefer-dist "laravel/laravel:^12.0" "${HOST_DIR}" --no-interaction --no-scripts --no-progress
fi

cd "${HOST_DIR}"

# 2) Wire path repository to the mounted package, set dev stability,
#    and ignore the Saloon advisories that would otherwise block install.
if ! grep -q '"/package"' composer.json; then
    echo "==> Wiring composer.json (path repo, stability, audit ignore)"
    php -r '
        $f = "composer.json";
        $j = json_decode(file_get_contents($f), true);
        $j["repositories"] = $j["repositories"] ?? [];
        $exists = false;
        foreach ($j["repositories"] as $r) {
            if (($r["url"] ?? null) === "/package") { $exists = true; break; }
        }
        if (!$exists) {
            $j["repositories"][] = ["type" => "path", "url" => "/package", "options" => ["symlink" => true]];
        }
        $j["minimum-stability"] = "dev";
        $j["prefer-stable"] = true;
        $j["config"] = $j["config"] ?? [];
        $j["config"]["audit"] = ["ignore" => ["PKSA-xnj5-w74d-6wmz", "PKSA-rnpm-45mg-w6ht", "PKSA-5szq-gvrg-ttfq"]];
        file_put_contents($f, json_encode($j, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    '
fi

# 3) Require the package (auto-discovery activates the provider)
if [ ! -d "${HOST_DIR}/vendor/antoniovila/quotes-bridge" ]; then
    echo "==> composer require antoniovila/quotes-bridge"
    composer require antoniovila/quotes-bridge:@dev --no-interaction --no-progress -W
else
    echo "==> Refreshing autoload"
    composer dump-autoload --no-interaction
fi

# 4) Build the Vue UI inside the package
cd "${PACKAGE_DIR}"
if [ ! -d node_modules ]; then
    echo "==> npm install"
    npm install --no-audit --no-fund --silent
fi
echo "==> npm run build"
npm run build

# 5) Publish package assets and config to the host app
cd "${HOST_DIR}"
echo "==> Publishing assets and config"
php artisan vendor:publish --tag=quotes-bridge-assets --force --no-interaction
php artisan vendor:publish --tag=quotes-config --force --no-interaction

# 6) Ensure .env exists with an APP_KEY
if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi
if [ -f .env ] && ! grep -q '^APP_KEY=base64' .env; then
    php artisan key:generate --no-interaction --force
fi

# Use file cache (Laravel 12 defaults to database; this test env has no DB)
if [ -f .env ]; then
    if grep -q '^CACHE_STORE=' .env; then
        sed -i 's/^CACHE_STORE=.*/CACHE_STORE=file/' .env
    else
        echo 'CACHE_STORE=file' >> .env
    fi
    php artisan config:clear --no-interaction || true
fi

# 7) Permissions for storage / bootstrap-cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "==> Bootstrap complete. Starting supervisord."

exec /usr/bin/supervisord -c /etc/supervisord.conf
