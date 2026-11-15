<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { Search as SearchIcon } from '@lucide/vue';
import PostCard from '@/components/frontend/PostCard.vue';

const props = defineProps({
    query: { type: String, default: '' },
    posts: { type: Array, default: () => [] },
    pages: { type: Array, default: () => [] },
    total: { type: Number, default: 0 },
});

const form = useForm({ q: props.query });
</script>

<template>
    <div class="mx-auto flex max-w-3xl flex-col gap-8">
        <header class="flex flex-col gap-4">
            <h1 class="text-3xl font-bold tracking-tight">Search</h1>

            <form action="/search" method="get" class="relative" role="search">
                <SearchIcon
                    class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-black/40"
                />
                <input
                    v-model="form.q"
                    type="search"
                    name="q"
                    placeholder="Search articles and pages…"
                    aria-label="Search"
                    class="w-full rounded-lg border border-black/15 bg-transparent py-2.5 pr-3 pl-9 text-sm focus:ring-2 focus:ring-[var(--fe-primary)] focus:outline-none"
                />
            </form>

            <p v-if="query" class="text-sm text-black/60">
                {{ total }} result{{ total === 1 ? '' : 's' }} for
                <strong class="text-black/80">“{{ query }}”</strong>
            </p>
        </header>

        <section v-if="posts.length" class="flex flex-col gap-6">
            <h2
                class="text-sm font-semibold tracking-wide text-black/50 uppercase"
            >
                Articles
            </h2>
            <div class="grid gap-6 sm:grid-cols-2">
                <PostCard v-for="post in posts" :key="post.id" :post="post" />
            </div>
        </section>

        <section v-if="pages.length" class="flex flex-col gap-4">
            <h2
                class="text-sm font-semibold tracking-wide text-black/50 uppercase"
            >
                Pages
            </h2>
            <ul class="flex flex-col gap-3">
                <li v-for="item in pages" :key="item.slug">
                    <Link
                        :href="item.url"
                        class="font-medium hover:text-[var(--fe-primary)]"
                    >
                        {{ item.title }}
                    </Link>
                    <p v-if="item.excerpt" class="text-sm text-black/60">
                        {{ item.excerpt }}
                    </p>
                </li>
            </ul>
        </section>

        <p
            v-if="query && !total"
            class="rounded-xl border border-dashed border-black/15 p-10 text-center text-black/60"
        >
            No results found. Try a different search term.
        </p>

        <p v-if="!query" class="text-center text-black/50">
            Type a term above to search the site.
        </p>
    </div>
</template>
