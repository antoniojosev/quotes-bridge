<script setup lang="ts">
import { onMounted } from 'vue'
import { useQuotes } from '../composables/useQuotes'
import QuoteCard from './QuoteCard.vue'
import Pagination from './Pagination.vue'

const {
    quotes,
    page,
    totalPages,
    loading,
    error,
    load,
    next,
    prev,
} = useQuotes(10)

onMounted(() => {
    void load()
})
</script>

<template>
    <section class="qb-list">
        <h2>Browse quotes</h2>

        <p v-if="loading" class="qb-status">Loading…</p>
        <p v-else-if="error" class="qb-status qb-status--error">{{ error }}</p>
        <p v-else-if="quotes.length === 0" class="qb-status">No quotes available.</p>
        <ul v-else class="qb-list__items">
            <li v-for="quote in quotes" :key="quote.id">
                <QuoteCard :quote="quote" />
            </li>
        </ul>

        <Pagination
            :page="page"
            :total-pages="totalPages"
            :disabled="loading"
            @prev="prev"
            @next="next"
        />
    </section>
</template>

<style scoped>
.qb-list h2 {
    font-size: 1.1rem;
    margin: 0 0 0.75rem;
}

.qb-list__items {
    list-style: none;
    padding: 0;
    margin: 0;
}

.qb-status {
    color: #6b7280;
    text-align: center;
    padding: 1rem;
}

.qb-status--error {
    color: #dc2626;
}
</style>
