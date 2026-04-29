export interface Quote {
    id: number
    quote: string
    author: string
}

export interface PageMeta {
    page: number
    per_page: number
    total: number
}

export interface QuotesPage {
    data: Quote[]
    meta: PageMeta
}

const API_BASE = '/api/quotes'

export async function fetchQuotes(page: number, perPage: number): Promise<QuotesPage> {
    const url = `${API_BASE}?page=${page}&per_page=${perPage}`
    const res = await fetch(url, {
        headers: { Accept: 'application/json' },
    })
    if (!res.ok) {
        throw new Error(`Failed to fetch quotes (status ${res.status}).`)
    }
    return (await res.json()) as QuotesPage
}

export async function fetchQuote(id: number): Promise<Quote | null> {
    const res = await fetch(`${API_BASE}/${id}`, {
        headers: { Accept: 'application/json' },
    })
    if (res.status === 404) return null
    if (!res.ok) {
        throw new Error(`Failed to fetch quote ${id} (status ${res.status}).`)
    }
    const body = (await res.json()) as { data: Quote }
    return body.data ?? null
}
