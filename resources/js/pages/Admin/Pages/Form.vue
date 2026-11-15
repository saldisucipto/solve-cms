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
import { dashboard } from '@/routes/admin';
import {
    index,
    store,
    edit,
    update as updateRoute,
} from '@/routes/admin/pages';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Pages', href: index() },
        ],
    },
});

const props = defineProps({
    page: { type: Object, default: null },
    options: { type: Object, default: () => ({}) },
});

const isEditing = computed(() => props.page !== null);

const siteUrl = computed(() => usePage().props.siteUrl ?? '');

const form = useForm({
    title: props.page?.title ?? '',
    slug: props.page?.slug ?? '',
    excerpt: props.page?.excerpt ?? '',
    content: props.page?.content ?? '',
    template: props.page?.template ?? 'default',
    status: props.page?.status ?? 'draft',
    parent_id: props.page?.parent_id ?? null,
    order: props.page?.order ?? 0,
    featured_media_id: props.page?.featured_media_id ?? null,
    seo: {
        title: props.page?.seo?.title ?? '',
        description: props.page?.seo?.description ?? '',
        focus_keyword: props.page?.seo?.focus_keyword ?? '',
        canonical_url: props.page?.seo?.canonical_url ?? '',
        robots: props.page?.seo?.robots ?? 'index,follow',
    },
});

function submit() {
    if (isEditing.value) {
        form.put(updateRoute(props.page.id), { preserveScroll: true });
    } else {
        form.post(store(), { preserveScroll: true });
    }
}
</script>

<template>
    <Head :title="isEditing ? 'Edit Page' : 'New Page'" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader
            :title="isEditing ? 'Edit Page' : 'New Page'"
            :description="isEditing ? page.title : 'Create a new static page.'"
        />

        <form @submit.prevent="submit" class="grid gap-6 lg:grid-cols-3">
            <div class="flex flex-col gap-4 lg:col-span-2">
                <Card>
                    <CardContent class="flex flex-col gap-4 pt-4">
                        <div class="flex flex-col gap-2">
                            <Label for="title">Title</Label>
                            <Input
                                id="title"
                                v-model="form.title"
                                placeholder="Page title"
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
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label for="excerpt">Excerpt</Label>
                            <Textarea
                                id="excerpt"
                                v-model="form.excerpt"
                                :rows="2"
                            />
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label for="content">Content</Label>
                            <RichTextEditor
                                v-model="form.content"
                                placeholder="Write your page content…"
                            />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm font-medium"
                            >SEO Settings</CardTitle
                        >
                    </CardHeader>
                    <CardContent class="flex flex-col gap-4">
                        <div class="flex flex-col gap-2">
                            <Label for="seo-title">SEO Title</Label>
                            <Input id="seo-title" v-model="form.seo.title" />
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
                                    <SelectItem value="published"
                                        >Published</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label for="template">Template</Label>
                            <Select v-model="form.template">
                                <SelectTrigger id="template">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="tpl in options.templates"
                                        :key="tpl.value"
                                        :value="tpl.value"
                                    >
                                        {{ tpl.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label for="parent">Parent Page</Label>
                            <Select v-model="form.parent_id">
                                <SelectTrigger id="parent">
                                    <SelectValue placeholder="No parent" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="none"
                                        >No parent</SelectItem
                                    >
                                    <SelectItem
                                        v-for="p in options.parents"
                                        :key="p.id"
                                        :value="p.id"
                                    >
                                        {{ p.title }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label for="order">Order</Label>
                            <Input
                                id="order"
                                v-model="form.order"
                                type="number"
                                min="0"
                            />
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label>Featured image</Label>
                            <MediaPicker
                                v-model="form.featured_media_id"
                                label="Choose image"
                            />
                            <InputError
                                :message="form.errors.featured_media_id"
                            />
                        </div>

                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full"
                        >
                            <Save class="size-4" />
                            {{ isEditing ? 'Update Page' : 'Save Page' }}
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </form>
    </div>
</template>
