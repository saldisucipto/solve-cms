<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="mx-auto flex max-w-xl flex-col items-center gap-5 py-16 text-center"
    >
        <p class="text-6xl font-bold text-[var(--fe-primary)]">404</p>
        <h1 class="text-2xl font-semibold tracking-tight">Page not found</h1>
        <p class="text-black/60">
            The page you are looking for does not exist or has been moved.
        </p>
        <div class="flex gap-3">
            <Link
                href="/"
                class="rounded-md bg-[var(--fe-primary)] px-4 py-2 text-sm font-medium text-white"
            >
                Back to home
            </Link>
            <Link
                href="/blog"
                class="rounded-md border border-black/15 px-4 py-2 text-sm font-medium"
            >
                Read the blog
            </Link>
        </div>
    </div>
</template>
