<script setup>
defineProps({
    comments: { type: Array, default: () => [] },
    depth: { type: Number, default: 0 },
});
</script>

<template>
    <ul
        class="flex flex-col gap-4"
        :class="depth > 0 ? 'mt-4 border-l border-black/10 pl-4' : ''"
    >
        <li v-for="comment in comments" :key="comment.id" class="text-sm">
            <div class="flex flex-wrap items-baseline gap-2">
                <strong class="font-semibold">{{ comment.author_name }}</strong>
                <span class="text-xs text-black/40">{{
                    comment.created_at
                }}</span>
            </div>
            <p class="mt-1 whitespace-pre-line text-black/80">
                {{ comment.content }}
            </p>

            <CommentList
                v-if="comment.replies?.length"
                :comments="comment.replies"
                :depth="depth + 1"
            />
        </li>
    </ul>
</template>
