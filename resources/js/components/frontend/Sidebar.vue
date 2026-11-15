<script setup>
defineProps({
    sidebar: { type: Object, default: () => ({}) },
});
</script>

<template>
    <aside class="flex flex-col gap-8">
        <section v-if="sidebar.categories?.length">
            <h2 class="mb-3 text-sm font-semibold tracking-wide uppercase">
                Categories
            </h2>
            <ul class="flex flex-col gap-1.5 text-sm">
                <li v-for="category in sidebar.categories" :key="category.slug">
                    <a
                        :href="`/category/${category.slug}`"
                        class="flex items-center justify-between hover:text-[var(--fe-primary)]"
                    >
                        <span>{{ category.name }}</span>
                        <span class="text-xs text-black/40">{{
                            category.posts_count
                        }}</span>
                    </a>
                </li>
            </ul>
        </section>

        <section v-if="sidebar.tags?.length">
            <h2 class="mb-3 text-sm font-semibold tracking-wide uppercase">
                Tags
            </h2>
            <div class="flex flex-wrap gap-2">
                <a
                    v-for="tag in sidebar.tags"
                    :key="tag.slug"
                    :href="`/tag/${tag.slug}`"
                    class="rounded-full border border-black/15 px-2.5 py-1 text-xs hover:border-[var(--fe-primary)]"
                >
                    {{ tag.name }}
                </a>
            </div>
        </section>

        <section v-if="sidebar.recent?.length">
            <h2 class="mb-3 text-sm font-semibold tracking-wide uppercase">
                Recent posts
            </h2>
            <ul class="flex flex-col gap-3 text-sm">
                <li v-for="post in sidebar.recent" :key="post.slug">
                    <a
                        :href="`/blog/${post.slug}`"
                        class="hover:text-[var(--fe-primary)]"
                    >
                        {{ post.title }}
                    </a>
                    <p class="text-xs text-black/40">
                        {{ post.published_human }}
                    </p>
                </li>
            </ul>
        </section>
    </aside>
</template>
