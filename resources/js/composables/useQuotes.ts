import { computed, ref } from 'vue'
import type { Quote } from '../api/client'
import { fetchQuotes } from '../api/client'

export function useQuotes(initialPerPage = 20) {
    const quotes = ref<Quote[]>([])
    const page = ref(1)
    const perPage = ref(initialPerPage)
    const total = ref(0)
    const loading = ref(false)
    const error = ref<string | null>(null)

    const totalPages = computed(() =>
        perPage.value > 0 ? Math.max(1, Math.ceil(total.value / perPage.value)) : 1
    )

    async function load(toPage: number = page.value): Promise<void> {
        loading.value = true
        error.value = null
        try {
            const result = await fetchQuotes(toPage, perPage.value)
            quotes.value = result.data
            page.value = result.meta.page
            perPage.value = result.meta.per_page
            total.value = result.meta.total
        } catch (e) {
            error.value = e instanceof Error ? e.message : 'Unknown error'
        } finally {
            loading.value = false
        }
    }

    function next(): void {
        if (page.value < totalPages.value) {
            void load(page.value + 1)
        }
    }

    function prev(): void {
        if (page.value > 1) {
            void load(page.value - 1)
        }
    }

    return {
        quotes,
        page,
        perPage,
        total,
        totalPages,
        loading,
        error,
        load,
        next,
        prev,
    }
}
