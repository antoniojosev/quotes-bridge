<script setup lang="ts">
import type { Quote } from '../api/client'

defineProps<{
    quote: Quote
}>()
</script>

<template>
    <article class="qb-card">
        <p class="qb-card__quote">{{ quote.quote }}</p>
        <footer class="qb-card__meta">
            <span class="qb-card__author">— {{ quote.author }}</span>
            <span class="qb-card__id">#{{ quote.id }}</span>
        </footer>
    </article>
</template>

<style scoped>
.qb-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1rem 1.25rem;
    margin-bottom: 0.75rem;
}

.qb-card__quote {
    margin: 0 0 0.5rem;
    font-size: 1rem;
    line-height: 1.5;
}

.qb-card__meta {
    display: flex;
    justify-content: space-between;
    color: #6b7280;
    font-size: 0.85rem;
}

.qb-card__author {
    font-style: italic;
}
</style>
