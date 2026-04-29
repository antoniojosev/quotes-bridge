<script setup lang="ts">
import { ref } from 'vue'
import type { Quote } from '../api/client'
import { fetchQuote } from '../api/client'
import QuoteCard from './QuoteCard.vue'

const idInput = ref<string>('')
const found = ref<Quote | null>(null)
const searched = ref(false)
const loading = ref(false)
const error = ref<string | null>(null)

async function search(): Promise<void> {
    error.value = null
    searched.value = false
    found.value = null

    const parsed = Number.parseInt(idInput.value, 10)
    if (Number.isNaN(parsed) || parsed <= 0) {
        error.value = 'Enter a positive integer.'
        return
    }

    loading.value = true
    try {
        found.value = await fetchQuote(parsed)
        searched.value = true
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Unknown error'
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <section class="qb-finder">
        <h2>Find by ID</h2>

        <form class="qb-finder__form" @submit.prevent="search">
            <input
                v-model="idInput"
                type="number"
                min="1"
                placeholder="Quote ID"
                :disabled="loading"
                class="qb-finder__input"
            >
            <button type="submit" :disabled="loading || idInput === ''">
                {{ loading ? 'Searching…' : 'Find' }}
            </button>
        </form>

        <p v-if="error" class="qb-status qb-status--error">{{ error }}</p>
        <QuoteCard v-else-if="found" :quote="found" />
        <p v-else-if="searched" class="qb-status">No quote with that ID.</p>
    </section>
</template>

<style scoped>
.qb-finder {
    margin-bottom: 1.5rem;
}

.qb-finder h2 {
    font-size: 1.1rem;
    margin: 0 0 0.75rem;
}

.qb-finder__form {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.qb-finder__input {
    flex: 1;
    padding: 0.4rem 0.6rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font: inherit;
}

.qb-finder button {
    padding: 0.4rem 0.9rem;
    border: 1px solid #1f2937;
    background: #1f2937;
    color: white;
    border-radius: 6px;
    cursor: pointer;
    font: inherit;
}

.qb-finder button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.qb-status {
    color: #6b7280;
    padding: 0.5rem 0;
}

.qb-status--error {
    color: #dc2626;
}
</style>
