<script setup lang="ts">
defineProps<{
    page: number
    totalPages: number
    disabled?: boolean
}>()

defineEmits<{
    (e: 'prev'): void
    (e: 'next'): void
}>()
</script>

<template>
    <nav class="qb-pagination">
        <button
            type="button"
            :disabled="disabled || page <= 1"
            @click="$emit('prev')"
        >
            Prev
        </button>
        <span class="qb-pagination__indicator">
            Page {{ page }} of {{ totalPages }}
        </span>
        <button
            type="button"
            :disabled="disabled || page >= totalPages"
            @click="$emit('next')"
        >
            Next
        </button>
    </nav>
</template>

<style scoped>
.qb-pagination {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    justify-content: center;
    padding: 1rem 0;
}

.qb-pagination button {
    padding: 0.4rem 0.9rem;
    border: 1px solid #d1d5db;
    background: white;
    border-radius: 6px;
    cursor: pointer;
    font: inherit;
}

.qb-pagination button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.qb-pagination__indicator {
    color: #6b7280;
    font-size: 0.9rem;
}
</style>
