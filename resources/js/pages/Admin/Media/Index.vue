<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { FileText, Image, Music, Trash2, Video } from '@lucide/vue';
import { ref } from 'vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input, InputError } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { dashboard } from '@/routes/admin';
import {
    index,
    store,
    update as updateRoute,
    destroy,
} from '@/routes/admin/media';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Media Library', href: index() },
        ],
    },
});

const props = defineProps({
    media: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    can: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');
const type = ref(props.filters.type || 'all');
const selected = ref(null);
const editForm = useForm({ title: '', alt_text: '', caption: '' });
const uploadForm = useForm({ file: null, title: '', alt_text: '' });
const fileInput = ref(null);

function applyFilters() {
    router.get(
        index(),
        { search: search.value, type: type.value },
        { preserveState: true, replace: true },
    );
}

function selectItem(item) {
    selected.value = item;
    editForm.title = item.title ?? '';
    editForm.alt_text = item.alt_text ?? '';
    editForm.caption = item.caption ?? '';
}

function saveEdit() {
    editForm.patch(updateRoute(selected.value.id), {
        onSuccess: () => {
            selected.value = null;
        },
        preserveScroll: true,
    });
}

function triggerUpload() {
    fileInput.value?.click();
}

function onFileSelected(event) {
    const file = event.target.files?.[0];
    if (!file) return;
    uploadForm.file = file;
    uploadForm.post(store(), {
        onSuccess: () => {
            uploadForm.reset();
            if (fileInput.value) fileInput.value.value = '';
        },
        preserveScroll: true,
    });
}

function confirmDelete(item) {
    if (confirm(`Delete "${item.title ?? item.original_name}"?`)) {
        if (selected.value?.id === item.id) selected.value = null;
        router.delete(destroy(item.id));
    }
}

function typeIcon(mime) {
    if (!mime) return FileText;
    if (mime.startsWith('image/')) return Image;
    if (mime.startsWith('video/')) return Video;
    if (mime.startsWith('audio/')) return Music;
    return FileText;
}
</script>

<template>
    <Head title="Media Library" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader
            title="Media Library"
            description="Upload and manage media files."
        >
            <template #actions>
                <Button v-if="can.upload" @click="triggerUpload">
                    <Image class="size-4" />
                    Upload
                </Button>
                <input
                    ref="fileInput"
                    type="file"
                    class="hidden"
                    accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx"
                    @change="onFileSelected"
                />
            </template>
        </PageHeader>

        <!-- Upload progress -->
        <div
            v-if="uploadForm.processing"
            class="rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm text-blue-700 dark:border-blue-800 dark:bg-blue-950 dark:text-blue-300"
        >
            Uploading file…
        </div>
        <InputError :message="uploadForm.errors.file" />

        <Card>
            <CardContent class="flex flex-wrap gap-3 pt-4">
                <Input
                    v-model="search"
                    placeholder="Search…"
                    class="w-56"
                    @keydown.enter="applyFilters"
                />
                <Select v-model="type" @update:model-value="applyFilters">
                    <SelectTrigger class="w-36">
                        <SelectValue placeholder="All types" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All types</SelectItem>
                        <SelectItem value="image">Images</SelectItem>
                        <SelectItem value="video">Videos</SelectItem>
                        <SelectItem value="audio">Audio</SelectItem>
                        <SelectItem value="document">Documents</SelectItem>
                    </SelectContent>
                </Select>
                <Button variant="secondary" @click="applyFilters"
                    >Search</Button
                >
            </CardContent>
        </Card>

        <div class="grid gap-6" :class="selected ? 'lg:grid-cols-3' : ''">
            <!-- Grid -->
            <div :class="selected ? 'lg:col-span-2' : ''">
                <div
                    class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5"
                >
                    <button
                        v-for="item in media.data"
                        :key="item.id"
                        class="group relative overflow-hidden rounded-lg border bg-muted transition-all hover:ring-2 hover:ring-primary"
                        :class="
                            selected?.id === item.id
                                ? 'ring-2 ring-primary'
                                : ''
                        "
                        @click="selectItem(item)"
                    >
                        <img
                            v-if="item.thumbnail || item.url"
                            :src="item.thumbnail ?? item.url"
                            :alt="item.alt_text"
                            class="aspect-square w-full object-cover"
                        />
                        <div
                            v-else
                            class="flex aspect-square items-center justify-center"
                        >
                            <component
                                :is="typeIcon(item.mime_type)"
                                class="size-8 text-muted-foreground"
                            />
                        </div>
                        <div
                            class="invisible absolute inset-x-0 bottom-0 bg-black/60 px-1.5 py-1 text-[10px] text-white group-hover:visible"
                        >
                            {{ item.title ?? item.original_name }}
                        </div>

                        <button
                            v-if="can.delete"
                            class="invisible absolute top-1 right-1 rounded bg-black/60 p-0.5 text-white group-hover:visible hover:bg-destructive"
                            @click.stop="confirmDelete(item)"
                        >
                            <Trash2 class="size-3" />
                        </button>
                    </button>
                </div>

                <div
                    v-if="!media.data.length"
                    class="py-16 text-center text-muted-foreground"
                >
                    No media files found.
                </div>

                <!-- Pagination -->
                <div
                    v-if="media.last_page > 1"
                    class="mt-4 flex items-center justify-between text-sm text-muted-foreground"
                >
                    <span
                        >Showing {{ media.from }}–{{ media.to }} of
                        {{ media.total }}</span
                    >
                    <div class="flex gap-1">
                        <Button
                            v-for="link in media.links"
                            :key="link.label"
                            :variant="link.active ? 'default' : 'ghost'"
                            size="sm"
                            :disabled="!link.url"
                            as-child
                        >
                            <a
                                v-if="link.url"
                                :href="link.url"
                                v-html="link.label"
                            />
                            <span v-else v-html="link.label" />
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Detail panel -->
            <div v-if="selected">
                <Card>
                    <CardContent class="flex flex-col gap-4 pt-4">
                        <img
                            v-if="selected.thumbnail || selected.url"
                            :src="selected.thumbnail ?? selected.url"
                            :alt="selected.alt_text"
                            class="max-h-48 w-full rounded-lg object-contain"
                        />

                        <div class="text-xs text-muted-foreground">
                            <p>
                                <strong>File:</strong>
                                {{ selected.original_name }}
                            </p>
                            <p><strong>Size:</strong> {{ selected.size }}</p>
                            <p v-if="selected.width">
                                <strong>Dimensions:</strong>
                                {{ selected.width }} × {{ selected.height }}
                            </p>
                            <p>
                                <strong>Type:</strong> {{ selected.mime_type }}
                            </p>
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label>Title</Label>
                            <Input v-model="editForm.title" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <Label>Alt Text</Label>
                            <Input
                                v-model="editForm.alt_text"
                                placeholder="Describe the image"
                            />
                        </div>
                        <div class="flex flex-col gap-2">
                            <Label>Caption</Label>
                            <Input v-model="editForm.caption" />
                        </div>

                        <div class="flex gap-2">
                            <Button
                                @click="saveEdit"
                                :disabled="editForm.processing"
                                class="flex-1"
                            >
                                Save
                            </Button>
                            <Button variant="ghost" @click="selected = null"
                                >Close</Button
                            >
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
