<script setup>
import { Link } from '@inertiajs/vue3';
import CommentForm from '@/components/frontend/CommentForm.vue';
import CommentList from '@/components/frontend/CommentList.vue';
import PostCard from '@/components/frontend/PostCard.vue';

defineProps({
    post: { type: Object, required: true },
    comments: { type: Array, default: () => [] },
    commentsCount: { type: Number, default: 0 },
    related: { type: Array, default: () => [] },
});
</script>

<template>
    <div class="flex flex-col gap-10">
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
            <span class="line-clamp-1 text-black/80">{{ post.title }}</span>
        </nav>

        <article class="flex flex-col gap-6">
            <header class="flex flex-col gap-3">
                <div
                    v-if="post.categories.length"
                    class="flex flex-wrap gap-2 text-xs"
                >
                    <Link
                        v-for="category in post.categories"
                        :key="category.slug"
                        :href="`/category/${category.slug}`"
                        class="font-medium text-[var(--fe-primary)] uppercase"
                    >
                        {{ category.name }}
                    </Link>
                </div>

                <h1
                    class="text-3xl leading-tight font-bold tracking-tight sm:text-4xl"
                >
                    {{ post.title }}
                </h1>

                <div
                    class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-black/50"
                >
                    <span v-if="post.author">{{ post.author }}</span>
                    <span v-if="post.published_human"
                        >· {{ post.published_human }}</span
                    >
                    <span>· {{ post.reading_time }} min read</span>
                </div>
            </header>

            <img
                v-if="post.image"
                :src="post.image"
                :alt="post.image_alt"
                class="w-full rounded-xl object-cover"
            />

            <div
                class="prose-content max-w-none text-[1.05rem] leading-relaxed [&_a]:text-[var(--fe-primary)] [&_a]:underline [&_blockquote]:border-l-4 [&_blockquote]:border-black/10 [&_blockquote]:pl-4 [&_blockquote]:italic [&_h2]:mt-8 [&_h2]:text-2xl [&_h2]:font-semibold [&_h3]:mt-6 [&_h3]:text-xl [&_h3]:font-semibold [&_img]:rounded-lg [&_ol]:list-decimal [&_ol]:pl-6 [&_pre]:overflow-x-auto [&_pre]:rounded-lg [&_pre]:bg-black/5 [&_pre]:p-4 [&_ul]:list-disc [&_ul]:pl-6"
                v-html="post.content"
            />

            <footer
                v-if="post.tags.length"
                class="flex flex-wrap items-center gap-2 border-t border-black/10 pt-4"
            >
                <span class="text-sm text-black/50">Tagged:</span>
                <Link
                    v-for="tag in post.tags"
                    :key="tag.slug"
                    :href="`/tag/${tag.slug}`"
                    class="rounded-full border border-black/15 px-3 py-1 text-xs hover:border-[var(--fe-primary)]"
                >
                    {{ tag.name }}
                </Link>
            </footer>
        </article>

        <section
            v-if="related.length"
            class="flex flex-col gap-6 border-t border-black/10 pt-8"
        >
            <h2 class="text-xl font-semibold tracking-tight">
                Related articles
            </h2>
            <div class="grid gap-6 sm:grid-cols-3">
                <PostCard v-for="item in related" :key="item.id" :post="item" />
            </div>
        </section>

        <section
            id="comments"
            class="flex flex-col gap-6 border-t border-black/10 pt-8"
        >
            <h2 class="text-xl font-semibold tracking-tight">
                Comments
                <span class="text-black/40">({{ commentsCount }})</span>
            </h2>

            <CommentList v-if="comments.length" :comments="comments" />
            <p v-else class="text-sm text-black/50">
                No comments yet. Be the first to share your thoughts.
            </p>

            <div class="rounded-xl border border-black/10 p-5">
                <h3 class="mb-3 font-medium">Leave a comment</h3>
                <CommentForm :post-slug="post.slug" />
            </div>
        </section>
    </div>
</template>
