<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    page: { type: Object, required: true },
});
</script>

<template>
    <article class="mx-auto flex max-w-3xl flex-col gap-6">
        <nav
            class="flex items-center gap-2 text-sm text-black/50"
            aria-label="Breadcrumb"
        >
            <Link href="/" class="hover:text-[var(--fe-primary)]">Home</Link>
            <span>/</span>
            <span class="text-black/80">{{ page.title }}</span>
        </nav>

        <header class="flex flex-col gap-3">
            <h1
                class="text-3xl leading-tight font-bold tracking-tight sm:text-4xl"
            >
                {{ page.title }}
            </h1>
            <p v-if="page.excerpt" class="text-lg text-black/60">
                {{ page.excerpt }}
            </p>
        </header>

        <img
            v-if="page.image"
            :src="page.image"
            :alt="page.image_alt"
            class="w-full rounded-xl object-cover"
        />

        <div
            class="max-w-none text-[1.05rem] leading-relaxed [&_a]:text-[var(--fe-primary)] [&_a]:underline [&_blockquote]:border-l-4 [&_blockquote]:border-black/10 [&_blockquote]:pl-4 [&_blockquote]:italic [&_h2]:mt-8 [&_h2]:text-2xl [&_h2]:font-semibold [&_h3]:mt-6 [&_h3]:text-xl [&_h3]:font-semibold [&_img]:rounded-lg [&_ol]:list-decimal [&_ol]:pl-6 [&_pre]:overflow-x-auto [&_pre]:rounded-lg [&_pre]:bg-black/5 [&_pre]:p-4 [&_ul]:list-disc [&_ul]:pl-6"
            v-html="page.content"
        />
    </article>
</template>
