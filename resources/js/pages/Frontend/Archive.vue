<script setup>
import { Link } from '@inertiajs/vue3';
import Pagination from '@/components/frontend/Pagination.vue';
import PostCard from '@/components/frontend/PostCard.vue';

defineProps({
    heading: { type: String, required: true },
    subheading: { type: String, default: null },
    kind: { type: String, default: 'category' },
    posts: { type: Object, required: true },
});
</script>

<template>
    <div class="flex flex-col gap-8">
        <nav
            class="flex flex-wrap items-center gap-2 text-sm text-black/50"
            aria-label="Breadcrumb"
        >
            <Link href="/" class="hover:text-[var(--fe-primary)]">Home</Link>
            <span>/</span>
            <Link href="/blog" class="hover:text-[var(--fe-primary)]"
                >Blog</Link
            >
            <span>/</span>
            <span class="text-black/80">{{ heading }}</span>
        </nav>

        <header>
            <p
                class="text-xs font-medium tracking-wide text-[var(--fe-primary)] uppercase"
            >
                {{ kind === 'tag' ? 'Tag' : 'Category' }}
            </p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight">
                {{ heading }}
            </h1>
            <p v-if="subheading" class="mt-2 text-black/60">{{ subheading }}</p>
        </header>

        <div
            v-if="posts.data.length"
            class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
        >
            <PostCard v-for="post in posts.data" :key="post.id" :post="post" />
        </div>

        <p
            v-else
            class="rounded-xl border border-dashed border-black/15 p-10 text-center text-black/60"
        >
            Nothing has been published here yet.
        </p>

        <Pagination :links="posts.links" />
    </div>
</template>
