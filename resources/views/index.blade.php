<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quotes Bridge</title>
    @php
        $manifestPath = public_path('vendor/quotes-bridge/manifest.json');
        $entry = null;
        if (file_exists($manifestPath)) {
            $manifest = json_decode((string) file_get_contents($manifestPath), true) ?? [];
            $entry = $manifest['resources/js/app.ts'] ?? null;
        }
    @endphp
    @if ($entry && ! empty($entry['css']))
        @foreach ($entry['css'] as $css)
            <link rel="stylesheet" href="/vendor/quotes-bridge/{{ $css }}">
        @endforeach
    @endif
</head>
<body>
    <div id="quotes-bridge-app"></div>
    @if ($entry)
        <script type="module" src="/vendor/quotes-bridge/{{ $entry['file'] }}"></script>
    @else
        <noscript-style>
            <style>
                body { font-family: system-ui, sans-serif; padding: 2rem; color: #6b7280; }
                code { background: #f3f4f6; padding: 0.1rem 0.4rem; border-radius: 4px; }
            </style>
        </noscript-style>
        <p style="font-family: system-ui, sans-serif; padding: 2rem; color: #6b7280;">
            Quotes Bridge UI assets have not been published yet. Run
            <code>php artisan vendor:publish --tag=quotes-bridge-assets</code>.
        </p>
    @endif
</body>
</html>
