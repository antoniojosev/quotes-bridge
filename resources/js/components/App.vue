<script setup lang="ts">
import QuoteFinder from './QuoteFinder.vue'
import QuoteList from './QuoteList.vue'
</script>

<template>
    <main class="qb-app">
        <header class="qb-header">
            <h1>Quotes Bridge</h1>
        </header>
        <QuoteFinder />
        <QuoteList />
    </main>
</template>

<style scoped>
.qb-app {
    font-family: system-ui, -apple-system, sans-serif;
    max-width: 720px;
    margin: 2rem auto;
    padding: 1rem;
    color: #1f2937;
}

.qb-header h1 {
    margin: 0 0 1.25rem;
    font-size: 1.5rem;
}
</style>
