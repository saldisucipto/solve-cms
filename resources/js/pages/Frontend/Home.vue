<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import PostCard from '@/components/frontend/PostCard.vue';

defineProps({
    featured: { type: Array, default: () => [] },
    latest: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
});

const page = usePage();
const layout = computed(() => page.props.site?.homepageLayout ?? 'featured');
const showFeatured = computed(
    () =>
        layout.value === 'featured' &&
        page.props.site?.showFeaturedSection !== false,
);
const latestLayout = computed(() =>
    page.props.site?.blogLayout === 'list' ? 'list' : 'grid',
);
</script>

<template>
    <div class="flex flex-col gap-12">
        <section
            v-if="showFeatured && featured.length"
            class="flex flex-col gap-6"
        >
            <PostCard :post="featured[0]" featured />
        </section>

        <section v-if="latest.length" class="flex flex-col gap-6">
            <div class="flex items-baseline justify-between">
                <h2 class="text-xl font-semibold tracking-tight">
                    Latest articles
                </h2>
                <Link
                    href="/blog"
                    class="text-sm text-[var(--fe-primary)] hover:underline"
                >
                    View all
                </Link>
            </div>

            <div
                class="grid gap-6"
                :class="
                    latestLayout === 'list'
                        ? 'grid-cols-1'
                        : 'sm:grid-cols-2 lg:grid-cols-3'
                "
            >
                <PostCard v-for="post in latest" :key="post.id" :post="post" />
            </div>
        </section>

        <section v-if="categories.length" class="flex flex-col gap-4">
            <h2 class="text-xl font-semibold tracking-tight">
                Browse by category
            </h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="category in categories"
                    :key="category.slug"
                    :href="`/category/${category.slug}`"
                    class="rounded-xl border border-black/10 p-5 transition-colors hover:border-[var(--fe-primary)]"
                >
                    <div class="flex items-center justify-between">
                        <h3 class="font-medium">{{ category.name }}</h3>
                        <span class="text-xs text-black/40">{{
                            category.posts_count
                        }}</span>
                    </div>
                    <p
                        v-if="category.description"
                        class="mt-1 text-sm text-black/60"
                    >
                        {{ category.description }}
                    </p>
                </Link>
            </div>
        </section>

        <section
            v-if="!latest.length"
            class="rounded-xl border border-dashed border-black/15 p-12 text-center"
        >
            <h2 class="font-medium">No content published yet</h2>
            <p class="mt-2 text-sm text-black/60">
                Publish a post from the dashboard and it will appear here.
            </p>
            <Link
                href="/admin"
                class="mt-4 inline-block rounded-md bg-[var(--fe-primary)] px-4 py-2 text-sm text-white"
            >
                Open dashboard
            </Link>
        </section>
    </div>
</template>
