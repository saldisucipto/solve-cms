<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    post: { type: Object, required: true },
    featured: { type: Boolean, default: false },
});

const page = usePage();
const showImage = computed(
    () =>
        page.props.site?.postCard !== 'text-only' && Boolean(props.post.image),
);
</script>

<template>
    <article
        class="fe-post-card group flex flex-col overflow-hidden rounded-xl border border-black/10 bg-white/50 transition-shadow hover:shadow-md"
        :class="featured ? 'sm:flex-row' : ''"
    >
        <a
            v-if="showImage"
            :href="post.url"
            class="block shrink-0 overflow-hidden"
            :class="featured ? 'sm:w-1/2' : ''"
        >
            <img
                :src="post.image"
                :alt="post.image_alt"
                loading="lazy"
                class="aspect-video w-full object-cover transition-transform duration-300 group-hover:scale-105"
                :class="featured ? 'sm:h-full' : ''"
            />
        </a>

        <div class="flex flex-1 flex-col gap-2 p-5">
            <div
                v-if="post.categories.length"
                class="flex flex-wrap gap-2 text-xs"
            >
                <a
                    v-for="category in post.categories"
                    :key="category.slug"
                    :href="`/category/${category.slug}`"
                    class="font-medium text-[var(--fe-primary)] uppercase"
                >
                    {{ category.name }}
                </a>
            </div>

            <h2
                class="font-semibold tracking-tight"
                :class="featured ? 'text-2xl' : 'text-lg'"
            >
                <a :href="post.url" class="hover:text-[var(--fe-primary)]">
                    {{ post.title }}
                </a>
            </h2>

            <p class="line-clamp-3 text-sm text-black/60">
                {{ post.excerpt }}
            </p>

            <div
                class="mt-auto flex flex-wrap items-center gap-x-3 gap-y-1 pt-2 text-xs text-black/50"
            >
                <span v-if="post.author">{{ post.author }}</span>
                <span v-if="post.published_human"
                    >· {{ post.published_human }}</span
                >
                <span>· {{ post.reading_time }} min read</span>
            </div>
        </div>
    </article>
</template>
