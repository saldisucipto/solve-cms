<script setup>
import { computed, ref, watch } from 'vue';
import { Image as ImageIcon, Search, Upload, X } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { requestJson } from '@/lib/http';

const props = defineProps({
    modelValue: { type: [Number, String], default: null },
    label: { type: String, default: 'Choose image' },
    allowUpload: { type: Boolean, default: true },
    compact: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'selected']);

const open = ref(false);
const loading = ref(false);
const uploading = ref(false);
const search = ref('');
const items = ref([]);
const errors = ref([]);
const fileInput = ref(null);

const selectedId = computed(() =>
    props.modelValue === null || props.modelValue === ''
        ? null
        : Number(props.modelValue),
);
const current = computed(
    () => items.value.find((item) => item.id === selectedId.value) ?? null,
);

async function loadLibrary() {
    loading.value = true;
    errors.value = [];

    try {
        const data = await requestJson(
            `/admin/media/library?search=${encodeURIComponent(search.value)}`,
        );

        items.value = data.items ?? [];
    } catch (error) {
        errors.value = error.messages?.() ?? [
            'Could not load the media library.',
        ];
    } finally {
        loading.value = false;
    }
}

function openPicker() {
    open.value = true;
    loadLibrary();
}

function choose(item) {
    emit('update:modelValue', item.id);
    emit('selected', item);
    open.value = false;
}

function clear() {
    emit('update:modelValue', null);
}

function triggerUpload() {
    fileInput.value?.click();
}

async function onFileSelected(event) {
    const file = event.target.files?.[0];

    if (!file) {
        return;
    }

    uploading.value = true;
    errors.value = [];

    const data = new FormData();
    data.append('file', file);

    try {
        const payload = await requestJson('/admin/media', {
            method: 'POST',
            body: data,
        });
        const media = payload.media;

        items.value = [media, ...items.value];
        emit('update:modelValue', media.id);
        emit('selected', media);
        open.value = false;
    } catch (error) {
        errors.value = error.messages?.() ?? ['Upload failed.'];
    } finally {
        uploading.value = false;
        event.target.value = '';
    }
}

watch(open, (value) => {
    if (value) {
        loadLibrary();
    }
});
</script>

<template>
    <div class="flex flex-col gap-2">
        <!-- Compact trigger (editor toolbar) -->
        <Button
            v-if="compact"
            type="button"
            variant="ghost"
            size="icon"
            class="size-7"
            :title="label"
            :aria-label="label"
            @click="openPicker"
        >
            <ImageIcon class="size-3.5" />
        </Button>

        <!-- Current selection -->
        <div
            v-if="!compact"
            class="flex items-center gap-3 rounded-md border p-2"
        >
            <div
                class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded bg-muted"
            >
                <img
                    v-if="current"
                    :src="current.thumbnail ?? current.url"
                    :alt="current.alt_text ?? current.title"
                    class="h-14 w-full object-cover"
                />
                <ImageIcon v-else class="size-5 text-muted-foreground" />
            </div>

            <div class="flex min-w-0 flex-1 flex-col">
                <span class="truncate text-sm">
                    {{ current ? current.title : 'No image selected' }}
                </span>
                <span
                    v-if="current"
                    class="truncate text-xs text-muted-foreground"
                >
                    {{ current.width }} × {{ current.height }}
                </span>
            </div>

            <div class="flex shrink-0 gap-1">
                <Button
                    type="button"
                    size="sm"
                    variant="outline"
                    @click="openPicker"
                >
                    {{ current ? 'Change' : label }}
                </Button>
                <Button
                    v-if="current"
                    type="button"
                    size="icon"
                    variant="ghost"
                    class="size-8 text-destructive hover:text-destructive"
                    title="Remove image"
                    @click="clear"
                >
                    <X class="size-4" />
                </Button>
            </div>
        </div>

        <input
            ref="fileInput"
            type="file"
            class="hidden"
            accept="image/*"
            @change="onFileSelected"
        />

        <ul
            v-if="errors.length"
            class="flex flex-col gap-1 text-xs text-destructive"
        >
            <li v-for="(message, index) in errors" :key="index">
                {{ message }}
            </li>
        </ul>

        <!-- Picker overlay -->
        <div
            v-if="open"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
        >
            <div class="absolute inset-0 bg-black/50" @click="open = false" />

            <div
                class="relative z-10 flex max-h-[85vh] w-full max-w-3xl flex-col overflow-hidden rounded-lg border bg-background shadow-xl"
            >
                <div class="flex items-center gap-3 border-b p-3">
                    <span class="text-sm font-medium">Media library</span>

                    <div class="relative ml-auto">
                        <Search
                            class="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            v-model="search"
                            class="w-48 pl-8"
                            placeholder="Search…"
                            @keydown.enter="loadLibrary"
                        />
                    </div>

                    <Button
                        v-if="allowUpload"
                        type="button"
                        size="sm"
                        :disabled="uploading"
                        @click="triggerUpload"
                    >
                        <Upload class="size-4" />
                        {{ uploading ? 'Uploading…' : 'Upload' }}
                    </Button>

                    <Button
                        type="button"
                        size="icon"
                        variant="ghost"
                        class="size-8"
                        @click="open = false"
                    >
                        <X class="size-4" />
                    </Button>
                </div>

                <div class="flex-1 overflow-y-auto p-3">
                    <p
                        v-if="loading"
                        class="py-10 text-center text-sm text-muted-foreground"
                    >
                        Loading images…
                    </p>

                    <p
                        v-else-if="!items.length"
                        class="py-10 text-center text-sm text-muted-foreground"
                    >
                        No images yet. Upload one to get started.
                    </p>

                    <div
                        v-else
                        class="grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-5"
                    >
                        <button
                            v-for="item in items"
                            :key="item.id"
                            type="button"
                            class="group relative overflow-hidden rounded-md border hover:ring-2 hover:ring-primary"
                            :class="
                                item.id === selectedId
                                    ? 'ring-2 ring-primary'
                                    : ''
                            "
                            @click="choose(item)"
                        >
                            <img
                                :src="item.thumbnail ?? item.url"
                                :alt="item.alt_text ?? item.title"
                                class="aspect-square w-full object-cover"
                                loading="lazy"
                            />
                            <span
                                class="absolute inset-x-0 bottom-0 truncate bg-black/60 px-1 py-0.5 text-[10px] text-white"
                            >
                                {{ item.title }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
