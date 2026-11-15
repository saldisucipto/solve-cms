<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Pagination from '@/components/frontend/Pagination.vue';
import PostCard from '@/components/frontend/PostCard.vue';
import Sidebar from '@/components/frontend/Sidebar.vue';

defineProps({
    posts: { type: Object, required: true },
    sidebar: { type: Object, default: () => ({}) },
});

const page = usePage();
const showSidebar = computed(() => page.props.site?.sidebarEnabled !== false);
const blogLayout = computed(() => page.props.site?.blogLayout ?? 'grid');
</script>

<template>
    <div class="flex flex-col gap-8">
        <header>
            <h1 class="text-3xl font-bold tracking-tight">Blog</h1>
            <p class="mt-1 text-black/60">
                Every article published on this site.
            </p>
        </header>

        <div :class="showSidebar ? 'grid gap-10 lg:grid-cols-[1fr_280px]' : ''">
            <div>
                <div
                    v-if="posts.data.length"
                    class="grid gap-6"
                    :class="
                        blogLayout === 'list'
                            ? 'grid-cols-1'
                            : showSidebar
                              ? 'sm:grid-cols-2'
                              : 'sm:grid-cols-2 lg:grid-cols-3'
                    "
                >
                    <PostCard
                        v-for="post in posts.data"
                        :key="post.id"
                        :post="post"
                    />
                </div>

                <p
                    v-else
                    class="rounded-xl border border-dashed border-black/15 p-10 text-center text-black/60"
                >
                    No posts have been published yet.
                </p>

                <Pagination :links="posts.links" />
            </div>

            <Sidebar v-if="showSidebar" :sidebar="sidebar" />
        </div>
    </div>
</template>
