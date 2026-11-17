<script setup>
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Image as ImageIcon, Save, Trash2, Upload } from '@lucide/vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input, InputError } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { dashboard } from '@/routes/admin';
import { index, update } from '@/routes/admin/settings';
import { upload, destroy } from '@/routes/admin/settings/branding';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Website Settings', href: index() },
        ],
    },
});

const props = defineProps({
    settings: { type: Object, default: () => ({}) },
    timezones: { type: Array, default: () => [] },
});

const form = useForm({
    site_name: props.settings.site_name ?? '',
    site_tagline: props.settings.site_tagline ?? '',
    site_description: props.settings.site_description ?? '',
    site_url: props.settings.site_url ?? '',
    posts_per_page: props.settings.posts_per_page ?? 10,
    timezone: props.settings.timezone ?? 'UTC',
});

// Derived from the page props, so an upload redirect refreshes the preview
// without any local bookkeeping. The dashboard and the public website have
// separate logos.
const branding = computed(() => ({
    logo: props.settings.logo_path ?? null,
    dashboardLogo: props.settings.dashboard_logo_path ?? null,
    favicon: props.settings.favicon_path ?? null,
}));

const uploading = ref(null);
const fileInputs = { logo: null, dashboard_logo: null, favicon: null };

function saveSettings() {
    form.put(update(), { preserveScroll: true });
}

function pick(type) {
    fileInputs[type]?.click();
}

function uploadFile(event, type) {
    const file = event.target.files?.[0];

    if (!file) {
        return;
    }

    const data = new FormData();
    data.append('type', type);
    data.append('file', file);

    uploading.value = type;

    router.post(upload(), data, {
        forceFormData: true,
        preserveScroll: true,
        onFinish: () => {
            uploading.value = null;
            event.target.value = '';
        },
    });
}

function removeBranding(type) {
    if (!confirm(`Remove the site ${type}?`)) {
        return;
    }

    router.delete(destroy(), {
        data: { type },
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Website Settings" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader
            title="Website Settings"
            description="Identity and branding shared by the dashboard and the public website."
        />

        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Branding -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-sm font-medium">Branding</CardTitle>
                </CardHeader>
                <CardContent class="flex flex-col gap-6">
                    <div class="flex flex-col gap-2">
                        <Label>Public site logo</Label>
                        <p class="text-xs text-muted-foreground">
                            Shown in the header of the public website.
                        </p>
                        <div
                            class="flex items-center gap-4 rounded-md border border-dashed p-4"
                        >
                            <div
                                class="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded bg-muted"
                            >
                                <img
                                    v-if="branding.logo"
                                    :src="branding.logo"
                                    alt="Public site logo"
                                    class="max-h-16 max-w-full object-contain"
                                />
                                <ImageIcon
                                    v-else
                                    class="size-6 text-muted-foreground"
                                />
                            </div>

                            <div class="flex flex-col gap-2">
                                <div class="flex gap-2">
                                    <Button
                                        type="button"
                                        size="sm"
                                        :disabled="uploading === 'logo'"
                                        @click="pick('logo')"
                                    >
                                        <Upload class="size-4" />
                                        {{
                                            uploading === 'logo'
                                                ? 'Uploading…'
                                                : 'Upload'
                                        }}
                                    </Button>
                                    <Button
                                        v-if="branding.logo"
                                        type="button"
                                        size="sm"
                                        variant="ghost"
                                        class="text-destructive hover:text-destructive"
                                        @click="removeBranding('logo')"
                                    >
                                        <Trash2 class="size-4" />
                                        Remove
                                    </Button>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    PNG, JPG, SVG or WebP up to 4 MB.
                                </p>
                            </div>

                            <input
                                :ref="(el) => (fileInputs.logo = el)"
                                type="file"
                                data-type="logo"
                                class="hidden"
                                accept="image/*"
                                @change="uploadFile($event, 'logo')"
                            />
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label>Dashboard logo</Label>
                        <p class="text-xs text-muted-foreground">
                            Shown in the admin sidebar, the login screen and the
                            admin browser tab.
                        </p>
                        <div
                            class="flex items-center gap-4 rounded-md border border-dashed p-4"
                        >
                            <div
                                class="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded bg-muted"
                            >
                                <img
                                    v-if="branding.dashboardLogo"
                                    :src="branding.dashboardLogo"
                                    alt="Dashboard logo"
                                    class="max-h-16 max-w-full object-contain"
                                />
                                <ImageIcon
                                    v-else
                                    class="size-6 text-muted-foreground"
                                />
                            </div>

                            <div class="flex flex-col gap-2">
                                <div class="flex gap-2">
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        :disabled="
                                            uploading === 'dashboard_logo'
                                        "
                                        @click="pick('dashboard_logo')"
                                    >
                                        <Upload class="size-4" />
                                        {{
                                            uploading === 'dashboard_logo'
                                                ? 'Uploading…'
                                                : 'Upload'
                                        }}
                                    </Button>
                                    <Button
                                        v-if="branding.dashboardLogo"
                                        type="button"
                                        size="sm"
                                        variant="ghost"
                                        class="text-destructive hover:text-destructive"
                                        @click="
                                            removeBranding('dashboard_logo')
                                        "
                                    >
                                        <Trash2 class="size-4" />
                                        Remove
                                    </Button>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    Falls back to the built-in Solve mark when
                                    empty.
                                </p>
                            </div>

                            <input
                                :ref="(el) => (fileInputs.dashboard_logo = el)"
                                type="file"
                                data-type="dashboard_logo"
                                class="hidden"
                                accept="image/*"
                                @change="uploadFile($event, 'dashboard_logo')"
                            />
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label>Favicon</Label>
                        <div
                            class="flex items-center gap-4 rounded-md border border-dashed p-4"
                        >
                            <div
                                class="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded bg-muted"
                            >
                                <img
                                    v-if="branding.favicon"
                                    :src="branding.favicon"
                                    alt="Current favicon"
                                    class="max-h-12 max-w-full object-contain"
                                />
                                <ImageIcon
                                    v-else
                                    class="size-5 text-muted-foreground"
                                />
                            </div>

                            <div class="flex flex-col gap-2">
                                <div class="flex gap-2">
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        :disabled="uploading === 'favicon'"
                                        @click="pick('favicon')"
                                    >
                                        <Upload class="size-4" />
                                        {{
                                            uploading === 'favicon'
                                                ? 'Uploading…'
                                                : 'Upload favicon'
                                        }}
                                    </Button>
                                    <Button
                                        v-if="branding.favicon"
                                        type="button"
                                        size="sm"
                                        variant="ghost"
                                        class="text-destructive hover:text-destructive"
                                        @click="removeBranding('favicon')"
                                    >
                                        <Trash2 class="size-4" />
                                        Remove
                                    </Button>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    ICO, PNG or SVG. Shown in browser tabs.
                                </p>
                            </div>

                            <input
                                :ref="(el) => (fileInputs.favicon = el)"
                                type="file"
                                data-type="favicon"
                                class="hidden"
                                accept="image/*,.ico"
                                @change="uploadFile($event, 'favicon')"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Identity -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-sm font-medium"
                        >Site identity</CardTitle
                    >
                </CardHeader>
                <CardContent>
                    <form
                        class="flex flex-col gap-4"
                        @submit.prevent="saveSettings"
                    >
                        <div class="flex flex-col gap-2">
                            <Label for="site_name">Site name</Label>
                            <Input id="site_name" v-model="form.site_name" />
                            <InputError :message="form.errors.site_name" />
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label for="site_tagline">Tagline</Label>
                            <Input
                                id="site_tagline"
                                v-model="form.site_tagline"
                            />
                            <InputError :message="form.errors.site_tagline" />
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label for="site_description">Description</Label>
                            <Textarea
                                id="site_description"
                                v-model="form.site_description"
                                :rows="3"
                            />
                            <InputError
                                :message="form.errors.site_description"
                            />
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label for="site_url">Website URL</Label>
                            <Input
                                id="site_url"
                                v-model="form.site_url"
                                placeholder="https://example.com"
                            />
                            <p class="text-xs text-muted-foreground">
                                Used for canonical URLs, sitemaps and absolute
                                links.
                            </p>
                            <InputError :message="form.errors.site_url" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="flex flex-col gap-2">
                                <Label for="posts_per_page"
                                    >Posts per page</Label
                                >
                                <Input
                                    id="posts_per_page"
                                    v-model="form.posts_per_page"
                                    type="number"
                                    min="1"
                                    max="100"
                                />
                                <InputError
                                    :message="form.errors.posts_per_page"
                                />
                            </div>

                            <div class="flex flex-col gap-2">
                                <Label for="timezone">Timezone</Label>
                                <select
                                    id="timezone"
                                    v-model="form.timezone"
                                    class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                                >
                                    <option
                                        v-for="zone in timezones"
                                        :key="zone"
                                        :value="zone"
                                    >
                                        {{ zone }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.timezone" />
                            </div>
                        </div>

                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="self-start"
                        >
                            <Save class="size-4" />
                            Save settings
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
