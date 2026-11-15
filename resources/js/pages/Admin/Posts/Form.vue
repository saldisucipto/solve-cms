<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Save } from '@lucide/vue';
import { computed } from 'vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import MediaPicker from '@/components/admin/MediaPicker.vue';
import RichTextEditor from '@/components/admin/RichTextEditor.vue';
import SeoPanel from '@/components/admin/SeoPanel.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input, InputError } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Badge } from '@/components/ui/badge';
import { dashboard } from '@/routes/admin';
import {
    index,
    store,
    edit,
    update as updateRoute,
} from '@/routes/admin/posts';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Posts', href: index() },
        ],
    },
});

const props = defineProps({
    post: { type: Object, default: null },
    options: { type: Object, default: () => ({}) },
});

const isEditing = computed(() => props.post !== null);

const siteUrl = computed(() => usePage().props.siteUrl ?? '');

const form = useForm({
    title: props.post?.title ?? '',
    slug: props.post?.slug ?? '',
    excerpt: props.post?.excerpt ?? '',
    content: props.post?.content ?? '',
    status: props.post?.status ?? 'draft',
    published_at: props.post?.published_at ?? '',
    is_featured: props.post?.is_featured ?? false,
    featured_media_id: props.post?.featured_media_id ?? null,
    category_ids: props.post?.category_ids ?? [],
    tag_ids: props.post?.tag_ids ?? [],
    seo: {
        title: props.post?.seo?.title ?? '',
        description: props.post?.seo?.description ?? '',
        focus_keyword: props.post?.seo?.focus_keyword ?? '',
        canonical_url: props.post?.seo?.canonical_url ?? '',
        robots: props.post?.seo?.robots ?? 'index,follow',
        og_title: props.post?.seo?.og_title ?? '',
        og_description: props.post?.seo?.og_description ?? '',
        og_image: props.post?.seo?.og_image ?? '',
        twitter_card: props.post?.seo?.twitter_card ?? 'summary_large_image',
    },
});

function submit() {
    if (isEditing.value) {
        form.put(updateRoute(props.post.id), { preserveScroll: true });
    } else {
        form.post(store(), { preserveScroll: true });
    }
}

function toggleCategory(id) {
    const idx = form.category_ids.indexOf(id);
    if (idx === -1) form.category_ids.push(id);
    else form.category_ids.splice(idx, 1);
}

function toggleTag(id) {
    const idx = form.tag_ids.indexOf(id);
    if (idx === -1) form.tag_ids.push(id);
    else form.tag_ids.splice(idx, 1);
}
</script>

<template>
    <Head :title="isEditing ? 'Edit Post' : 'New Post'" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader
            :title="isEditing ? 'Edit Post' : 'New Post'"
            :description="isEditing ? post.title : 'Create a new blog post.'"
        />

        <form @submit.prevent="submit" class="grid gap-6 lg:grid-cols-3">
            <!-- Main content -->
            <div class="flex flex-col gap-4 lg:col-span-2">
                <Card>
                    <CardContent class="flex flex-col gap-4 pt-4">
                        <div class="flex flex-col gap-2">
                            <Label for="title">Title</Label>
                            <Input
                                id="title"
                                v-model="form.title"
                                placeholder="Post title"
                            />
                            <InputError :message="form.errors.title" />
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label for="slug">Slug</Label>
                            <Input
                                id="slug"
                                v-model="form.slug"
                                placeholder="Leave empty to auto-generate"
                            />
                            <InputError :message="form.errors.slug" />
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label for="excerpt">Excerpt</Label>
                            <Textarea
                                id="excerpt"
                                v-model="form.excerpt"
                                :rows="2"
                                placeholder="Short summary"
                            />
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label for="content">Content</Label>
                            <RichTextEditor
                                v-model="form.content"
                                placeholder="Write your post content…"
                            />
                            <InputError :message="form.errors.content" />
                        </div>
                    </CardContent>
                </Card>

                <!-- SEO Panel -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm font-medium"
                            >SEO Settings</CardTitle
                        >
                    </CardHeader>
                    <CardContent class="flex flex-col gap-4">
                        <div class="flex flex-col gap-2">
                            <Label for="seo-title">SEO Title</Label>
                            <Input
                                id="seo-title"
                                v-model="form.seo.title"
                                placeholder="SEO title"
                            />
                        </div>
                        <div class="flex flex-col gap-2">
                            <Label for="seo-desc">Meta Description</Label>
                            <Textarea
                                id="seo-desc"
                                v-model="form.seo.description"
                                :rows="3"
                            />
                        </div>
                        <div class="flex flex-col gap-2">
                            <Label for="seo-keyword">Focus Keyword</Label>
                            <Input
                                id="seo-keyword"
                                v-model="form.seo.focus_keyword"
                                placeholder="e.g. laravel cms"
                            />
                        </div>
                        <div class="flex flex-col gap-2">
                            <Label for="seo-canonical">Canonical URL</Label>
                            <Input
                                id="seo-canonical"
                                v-model="form.seo.canonical_url"
                                placeholder="Leave empty for default"
                            />
                        </div>
                    </CardContent>
                </Card>

                <SeoPanel
                    :title="form.title"
                    :seo-title="form.seo.title"
                    :description="form.seo.description"
                    :focus-keyword="form.seo.focus_keyword"
                    :slug="form.slug || form.title"
                    :content="form.content"
                    :canonical-url="form.seo.canonical_url"
                    :site-url="siteUrl"
                />
            </div>

            <!-- Sidebar -->
            <div class="flex flex-col gap-4">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm font-medium"
                            >Publish</CardTitle
                        >
                    </CardHeader>
                    <CardContent class="flex flex-col gap-4">
                        <div class="flex flex-col gap-2">
                            <Label for="status">Status</Label>
                            <Select v-model="form.status">
                                <SelectTrigger id="status">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="draft">Draft</SelectItem>
                                    <SelectItem value="pending"
                                        >Pending Review</SelectItem
                                    >
                                    <SelectItem value="published"
                                        >Published</SelectItem
                                    >
                                    <SelectItem value="scheduled"
                                        >Scheduled</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.status" />
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label for="published_at">Publish Date</Label>
                            <Input
                                id="published_at"
                                v-model="form.published_at"
                                type="datetime-local"
                            />
                        </div>

                        <label class="flex items-center gap-2">
                            <Checkbox
                                :model-value="form.is_featured"
                                @update:model-value="form.is_featured = $event"
                            />
                            <span class="text-sm">Featured post</span>
                        </label>

                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full"
                        >
                            <Save class="size-4" />
                            {{ isEditing ? 'Update Post' : 'Save Post' }}
                        </Button>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm font-medium"
                            >Featured image</CardTitle
                        >
                    </CardHeader>
                    <CardContent>
                        <MediaPicker
                            v-model="form.featured_media_id"
                            label="Choose image"
                        />
                        <p class="mt-2 text-xs text-muted-foreground">
                            Shown on post cards, the article header and social
                            previews.
                        </p>
                        <InputError :message="form.errors.featured_media_id" />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm font-medium"
                            >Categories</CardTitle
                        >
                    </CardHeader>
                    <CardContent class="flex flex-col gap-2">
                        <label
                            v-for="cat in options.categories"
                            :key="cat.id"
                            class="flex items-center gap-2 text-sm"
                        >
                            <Checkbox
                                :model-value="
                                    form.category_ids.includes(cat.id)
                                "
                                @update:model-value="toggleCategory(cat.id)"
                            />
                            {{ cat.name }}
                        </label>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm font-medium">Tags</CardTitle>
                    </CardHeader>
                    <CardContent class="flex flex-wrap gap-2">
                        <label
                            v-for="tag in options.tags"
                            :key="tag.id"
                            class="cursor-pointer"
                        >
                            <Badge
                                :variant="
                                    form.tag_ids.includes(tag.id)
                                        ? 'default'
                                        : 'outline'
                                "
                                class="select-none"
                                @click.prevent="toggleTag(tag.id)"
                            >
                                {{ tag.name }}
                            </Badge>
                        </label>
                    </CardContent>
                </Card>
            </div>
        </form>
    </div>
</template>
