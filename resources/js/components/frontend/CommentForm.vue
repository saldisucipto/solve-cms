<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    postSlug: { type: String, required: true },
    parentId: { type: Number, default: null },
    compact: { type: Boolean, default: false },
});

const form = useForm({
    author_name: '',
    author_email: '',
    content: '',
    parent_id: props.parentId,
});

function submit() {
    form.post(`/blog/${props.postSlug}/comments`, {
        preserveScroll: true,
        onSuccess: () => form.reset('content', 'parent_id'),
    });
}
</script>

<template>
    <form class="flex flex-col gap-3" @submit.prevent="submit">
        <div v-if="!parentId" class="grid gap-3 sm:grid-cols-2">
            <div class="flex flex-col gap-1">
                <label
                    :for="`name-${parentId ?? 'root'}`"
                    class="text-sm font-medium"
                >
                    Name
                </label>
                <input
                    :id="`name-${parentId ?? 'root'}`"
                    v-model="form.author_name"
                    type="text"
                    required
                    class="rounded-md border border-black/15 bg-transparent px-3 py-2 text-sm focus:ring-2 focus:ring-[var(--fe-primary)] focus:outline-none"
                />
                <p v-if="form.errors.author_name" class="text-xs text-red-600">
                    {{ form.errors.author_name }}
                </p>
            </div>

            <div class="flex flex-col gap-1">
                <label
                    :for="`email-${parentId ?? 'root'}`"
                    class="text-sm font-medium"
                >
                    Email <span class="text-black/40">(not published)</span>
                </label>
                <input
                    :id="`email-${parentId ?? 'root'}`"
                    v-model="form.author_email"
                    type="email"
                    required
                    class="rounded-md border border-black/15 bg-transparent px-3 py-2 text-sm focus:ring-2 focus:ring-[var(--fe-primary)] focus:outline-none"
                />
                <p v-if="form.errors.author_email" class="text-xs text-red-600">
                    {{ form.errors.author_email }}
                </p>
            </div>
        </div>

        <div class="flex flex-col gap-1">
            <label
                :for="`content-${parentId ?? 'root'}`"
                class="text-sm font-medium"
            >
                {{ compact ? 'Reply' : 'Comment' }}
            </label>
            <textarea
                :id="`content-${parentId ?? 'root'}`"
                v-model="form.content"
                :rows="compact ? 2 : 4"
                required
                class="rounded-md border border-black/15 bg-transparent px-3 py-2 text-sm focus:ring-2 focus:ring-[var(--fe-primary)] focus:outline-none"
            />
            <p v-if="form.errors.content" class="text-xs text-red-600">
                {{ form.errors.content }}
            </p>
        </div>

        <div>
            <button
                type="submit"
                :disabled="form.processing"
                class="rounded-md bg-[var(--fe-primary)] px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
            >
                {{
                    form.processing
                        ? 'Sending…'
                        : compact
                          ? 'Post reply'
                          : 'Post comment'
                }}
            </button>
            <span class="ml-3 text-xs text-black/50">
                Comments are reviewed before they appear.
            </span>
        </div>
    </form>
</template>
