<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ExternalLink, RotateCcw, Save, Upload } from '@lucide/vue';
import { computed, ref } from 'vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes/admin';
import { index as themesIndex } from '@/routes/admin/themes';
import {
    index as customizerIndex,
    draft,
    publish,
    reset,
} from '@/routes/admin/customizer';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Themes', href: themesIndex() },
            { title: 'Customizer', href: customizerIndex() },
        ],
    },
});

const props = defineProps({
    theme: { type: Object, required: true },
    settings: { type: Object, default: () => ({}) },
    defaults: { type: Object, default: () => ({}) },
    hasDraft: { type: Boolean, default: false },
    general: { type: Object, default: () => ({}) },
    images: { type: Array, default: () => [] },
    previewUrl: { type: String, default: '' },
});

const TABS = [
    { key: 'general', label: 'General' },
    { key: 'colors', label: 'Colors' },
    { key: 'typography', label: 'Typography' },
    { key: 'layout', label: 'Layout' },
    { key: 'components', label: 'Components' },
];

const activeTab = ref('general');

const form = useForm({
    site_name: props.general.site_name ?? '',
    site_tagline: props.general.site_tagline ?? '',
    site_description: props.general.site_description ?? '',
    logo_path: props.general.logo_path ?? '',
    favicon_path: props.general.favicon_path ?? '',
    appearance: {
        primary_color: props.settings.primary_color ?? '#2563eb',
        secondary_color: props.settings.secondary_color ?? '#0f172a',
        background_color: props.settings.background_color ?? '#ffffff',
        text_color: props.settings.text_color ?? '#0f172a',
        link_color: props.settings.link_color ?? '#2563eb',
        font_family: props.settings.font_family ?? 'Instrument Sans',
        heading_font: props.settings.heading_font ?? 'Instrument Sans',
        body_font: props.settings.body_font ?? 'Instrument Sans',
        font_size_base: props.settings.font_size_base ?? 16,
        line_height: props.settings.line_height ?? 1.7,
        container_width: props.settings.container_width ?? 1152,
        header_layout: props.settings.header_layout ?? 'classic',
        footer_layout: props.settings.footer_layout ?? 'simple',
        sidebar_enabled: props.settings.sidebar_enabled ?? true,
        blog_layout: props.settings.blog_layout ?? 'grid',
        homepage_layout: props.settings.homepage_layout ?? 'featured',
        post_card: props.settings.post_card ?? 'image-top',
        show_featured_section: props.settings.show_featured_section ?? true,
        show_search: props.settings.show_search ?? true,
    },
});

const templatesFor = (key) => props.theme.templates?.[key] ?? [];

const preview = computed(() => ({
    primary: form.appearance.primary_color,
    background: form.appearance.background_color,
    text: form.appearance.text_color,
    width: `${form.appearance.container_width}px`,
    size: `${form.appearance.font_size_base}px`,
    lineHeight: form.appearance.line_height,
    headingFont: form.appearance.heading_font,
    bodyFont: form.appearance.body_font,
}));

function saveDraft() {
    form.put(draft(), { preserveScroll: true, preserveState: true });
}

function publishChanges() {
    if (confirm('Publish these customisations to the live site?')) {
        form.post(publish(), { preserveScroll: true });
    }
}

function resetTheme() {
    if (
        confirm(
            'Reset this theme to its defaults? Published customisations will be lost.',
        )
    ) {
        router.post(reset(), {}, { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Theme Customizer" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader
            :title="`Customizer — ${theme.name}`"
            description="Changes are saved as a draft until you publish them."
        >
            <template #actions>
                <Badge v-if="hasDraft" variant="secondary"
                    >Unpublished draft</Badge
                >
                <Button variant="outline" as-child>
                    <Link :href="themesIndex()">Themes</Link>
                </Button>
                <Button variant="outline" as-child>
                    <a :href="previewUrl" target="_blank" rel="noopener">
                        <ExternalLink class="size-4" />
                        Preview full site
                    </a>
                </Button>
            </template>
        </PageHeader>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_420px]">
            <!-- Controls -->
            <div class="flex flex-col gap-4">
                <div class="flex flex-wrap gap-1 rounded-lg border p-1">
                    <button
                        v-for="tab in TABS"
                        :key="tab.key"
                        type="button"
                        class="rounded-md px-3 py-1.5 text-sm"
                        :class="
                            activeTab === tab.key
                                ? 'bg-primary text-primary-foreground'
                                : 'hover:bg-muted'
                        "
                        @click="activeTab = tab.key"
                    >
                        {{ tab.label }}
                    </button>
                </div>

                <!-- General -->
                <Card v-if="activeTab === 'general'">
                    <CardHeader
                        ><CardTitle class="text-sm font-medium"
                            >Site identity</CardTitle
                        ></CardHeader
                    >
                    <CardContent class="flex flex-col gap-4">
                        <div class="flex flex-col gap-2">
                            <Label>Site title</Label>
                            <Input v-model="form.site_name" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <Label>Tagline</Label>
                            <Input v-model="form.site_tagline" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <Label>Description</Label>
                            <Input v-model="form.site_description" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <Label>Logo</Label>
                            <select
                                v-model="form.logo_path"
                                class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                            >
                                <option value="">
                                    No logo (use site title)
                                </option>
                                <option
                                    v-for="image in images"
                                    :key="image.id"
                                    :value="image.url"
                                >
                                    {{ image.label }}
                                </option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-2">
                            <Label>Favicon</Label>
                            <select
                                v-model="form.favicon_path"
                                class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                            >
                                <option value="">Default favicon</option>
                                <option
                                    v-for="image in images"
                                    :key="image.id"
                                    :value="image.url"
                                >
                                    {{ image.label }}
                                </option>
                            </select>
                        </div>
                    </CardContent>
                </Card>

                <!-- Colors -->
                <Card v-if="activeTab === 'colors'">
                    <CardHeader
                        ><CardTitle class="text-sm font-medium"
                            >Colors</CardTitle
                        ></CardHeader
                    >
                    <CardContent class="grid gap-4 sm:grid-cols-2">
                        <div
                            v-for="field in [
                                ['primary_color', 'Primary'],
                                ['secondary_color', 'Secondary'],
                                ['background_color', 'Background'],
                                ['text_color', 'Text'],
                                ['link_color', 'Link'],
                            ]"
                            :key="field[0]"
                            class="flex flex-col gap-2"
                        >
                            <Label>{{ field[1] }}</Label>
                            <div class="flex gap-2">
                                <input
                                    v-model="form.appearance[field[0]]"
                                    type="color"
                                    class="h-9 w-12 rounded border border-input"
                                />
                                <Input
                                    v-model="form.appearance[field[0]]"
                                    class="font-mono text-xs"
                                />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Typography -->
                <Card v-if="activeTab === 'typography'">
                    <CardHeader
                        ><CardTitle class="text-sm font-medium"
                            >Typography</CardTitle
                        ></CardHeader
                    >
                    <CardContent class="grid gap-4 sm:grid-cols-2">
                        <div class="flex flex-col gap-2">
                            <Label>Base font family</Label>
                            <Input v-model="form.appearance.font_family" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <Label>Heading font</Label>
                            <Input v-model="form.appearance.heading_font" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <Label>Body font</Label>
                            <Input v-model="form.appearance.body_font" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <Label>Base font size (px)</Label>
                            <Input
                                v-model="form.appearance.font_size_base"
                                type="number"
                                min="12"
                                max="24"
                            />
                        </div>
                        <div class="flex flex-col gap-2">
                            <Label>Line height</Label>
                            <Input
                                v-model="form.appearance.line_height"
                                type="number"
                                step="0.05"
                                min="1"
                                max="2.5"
                            />
                        </div>
                    </CardContent>
                </Card>

                <!-- Layout -->
                <Card v-if="activeTab === 'layout'">
                    <CardHeader
                        ><CardTitle class="text-sm font-medium"
                            >Layout</CardTitle
                        ></CardHeader
                    >
                    <CardContent class="grid gap-4 sm:grid-cols-2">
                        <div class="flex flex-col gap-2">
                            <Label>Container width (px)</Label>
                            <Input
                                v-model="form.appearance.container_width"
                                type="number"
                                min="640"
                                max="1920"
                            />
                        </div>
                        <div class="flex flex-col gap-2">
                            <Label>Header layout</Label>
                            <select
                                v-model="form.appearance.header_layout"
                                class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                            >
                                <option
                                    v-for="option in templatesFor('header')"
                                    :key="option"
                                    :value="option"
                                >
                                    {{ option }}
                                </option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-2">
                            <Label>Footer layout</Label>
                            <select
                                v-model="form.appearance.footer_layout"
                                class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                            >
                                <option
                                    v-for="option in templatesFor('footer')"
                                    :key="option"
                                    :value="option"
                                >
                                    {{ option }}
                                </option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-2">
                            <Label>Blog layout</Label>
                            <select
                                v-model="form.appearance.blog_layout"
                                class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                            >
                                <option
                                    v-for="option in templatesFor('blog')"
                                    :key="option"
                                    :value="option"
                                >
                                    {{ option }}
                                </option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-2">
                            <Label>Homepage layout</Label>
                            <select
                                v-model="form.appearance.homepage_layout"
                                class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                            >
                                <option
                                    v-for="option in templatesFor('homepage')"
                                    :key="option"
                                    :value="option"
                                >
                                    {{ option }}
                                </option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-2">
                            <Label>Post card style</Label>
                            <select
                                v-model="form.appearance.post_card"
                                class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                            >
                                <option value="image-top">Image on top</option>
                                <option value="text-only">Text only</option>
                            </select>
                        </div>
                        <label class="flex items-center gap-2 sm:col-span-2">
                            <Checkbox
                                :model-value="form.appearance.sidebar_enabled"
                                @update:model-value="
                                    form.appearance.sidebar_enabled = $event
                                "
                            />
                            <span class="text-sm"
                                >Show sidebar on the blog</span
                            >
                        </label>
                    </CardContent>
                </Card>

                <!-- Components -->
                <Card v-if="activeTab === 'components'">
                    <CardHeader
                        ><CardTitle class="text-sm font-medium"
                            >Components</CardTitle
                        ></CardHeader
                    >
                    <CardContent class="flex flex-col gap-4">
                        <label class="flex items-center gap-2">
                            <Checkbox
                                :model-value="form.appearance.show_search"
                                @update:model-value="
                                    form.appearance.show_search = $event
                                "
                            />
                            <span class="text-sm"
                                >Show search in the header</span
                            >
                        </label>
                        <label class="flex items-center gap-2">
                            <Checkbox
                                :model-value="
                                    form.appearance.show_featured_section
                                "
                                @update:model-value="
                                    form.appearance.show_featured_section =
                                        $event
                                "
                            />
                            <span class="text-sm"
                                >Show the featured section on the homepage</span
                            >
                        </label>
                    </CardContent>
                </Card>

                <div class="flex flex-wrap gap-2">
                    <Button :disabled="form.processing" @click="publishChanges">
                        <Upload class="size-4" />
                        Publish
                    </Button>
                    <Button
                        variant="outline"
                        :disabled="form.processing"
                        @click="saveDraft"
                    >
                        <Save class="size-4" />
                        Save draft
                    </Button>
                    <Button
                        variant="ghost"
                        :disabled="form.processing"
                        @click="resetTheme"
                    >
                        <RotateCcw class="size-4" />
                        Reset to defaults
                    </Button>
                </div>
            </div>

            <!-- Live preview -->
            <div class="flex flex-col gap-3 xl:sticky xl:top-6 xl:self-start">
                <p
                    class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                >
                    Live preview
                </p>

                <div
                    class="overflow-hidden rounded-lg border shadow-sm"
                    :style="{
                        background: preview.background,
                        color: preview.text,
                        fontSize: preview.size,
                        lineHeight: preview.lineHeight,
                        fontFamily: preview.bodyFont,
                    }"
                >
                    <!-- Header -->
                    <div
                        class="flex items-center gap-3 border-b px-4 py-3"
                        :class="
                            form.appearance.header_layout === 'centered'
                                ? 'flex-col text-center'
                                : 'justify-between'
                        "
                        :style="{ borderColor: 'rgba(0,0,0,0.1)' }"
                    >
                        <div
                            class="font-semibold"
                            :style="{
                                fontFamily: preview.headingFont,
                                color: preview.text,
                            }"
                        >
                            {{ form.site_name || 'Solve CMS' }}
                        </div>
                        <div
                            class="flex items-center gap-3 text-xs"
                            :style="{ color: preview.primary }"
                        >
                            <span>Home</span>
                            <span>Blog</span>
                            <span v-if="form.appearance.show_search"
                                >Search</span
                            >
                        </div>
                    </div>

                    <div class="p-4">
                        <p
                            class="mb-2 text-[10px] tracking-wide uppercase"
                            :style="{ color: preview.primary }"
                        >
                            {{ form.appearance.homepage_layout }} layout
                        </p>

                        <!-- Featured -->
                        <div
                            v-if="form.appearance.show_featured_section"
                            class="mb-3 rounded-md p-3"
                            :style="{
                                background: preview.primary,
                                color: '#fff',
                            }"
                        >
                            <p class="text-sm font-semibold">
                                Featured article
                            </p>
                            <p class="text-xs opacity-80">
                                Highlighted on the homepage
                            </p>
                        </div>

                        <!-- Post cards -->
                        <div
                            class="grid gap-2"
                            :class="
                                form.appearance.blog_layout === 'list'
                                    ? 'grid-cols-1'
                                    : 'grid-cols-2'
                            "
                        >
                            <div
                                v-for="n in 2"
                                :key="n"
                                class="fe-post-card overflow-hidden rounded-md border"
                                :style="{ borderColor: 'rgba(0,0,0,0.12)' }"
                            >
                                <div
                                    v-if="
                                        form.appearance.post_card !==
                                        'text-only'
                                    "
                                    class="h-12"
                                    :style="{ background: 'rgba(0,0,0,0.08)' }"
                                />
                                <div class="p-2">
                                    <p class="text-xs font-medium">
                                        Sample article title
                                    </p>
                                    <p class="mt-1 text-[10px] opacity-60">
                                        Excerpt text
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="form.appearance.sidebar_enabled"
                            class="mt-3 rounded-md border p-2 text-[10px] opacity-70"
                            :style="{ borderColor: 'rgba(0,0,0,0.12)' }"
                        >
                            Sidebar enabled
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="border-t px-4 py-3 text-[10px] opacity-70"
                        :style="{ borderColor: 'rgba(0,0,0,0.1)' }"
                        :class="
                            form.appearance.footer_layout === 'columns'
                                ? 'flex justify-between'
                                : 'text-center'
                        "
                    >
                        <span
                            >© {{ new Date().getFullYear() }}
                            {{ form.site_name }}</span
                        >
                        <span>Powered by Solve CMS</span>
                    </div>
                </div>

                <p class="text-xs text-muted-foreground">
                    This preview mirrors the theme controls. Open the full-site
                    preview to see real content with your draft applied.
                </p>
            </div>
        </div>
    </div>
</template>
