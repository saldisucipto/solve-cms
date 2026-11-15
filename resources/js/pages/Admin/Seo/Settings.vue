<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { Save } from '@lucide/vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input, InputError } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes/admin';
import {
    index as seoIndex,
    settings as settingsRoute,
} from '@/routes/admin/seo';
import { update } from '@/routes/admin/seo/settings';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'SEO', href: seoIndex() },
            { title: 'Settings', href: settingsRoute() },
        ],
    },
});

const props = defineProps({
    settings: { type: Object, default: () => ({}) },
    can: { type: Object, default: () => ({}) },
});

const form = useForm({
    site_url: props.settings.site_url ?? '',
    seo_title_separator: props.settings.seo_title_separator ?? '|',
    seo_default_robots: props.settings.seo_default_robots ?? 'index,follow',
    seo_organization_name: props.settings.seo_organization_name ?? '',
    seo_twitter_handle: props.settings.seo_twitter_handle ?? '',
    seo_google_verification: props.settings.seo_google_verification ?? '',
    seo_sitemap_enabled: props.settings.seo_sitemap_enabled ?? true,
});

function submit() {
    form.put(update(), { preserveScroll: true });
}
</script>

<template>
    <Head title="SEO Settings" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader
            title="SEO Settings"
            description="Technical defaults applied across the website."
        />

        <form class="grid gap-6 lg:grid-cols-2" @submit.prevent="submit">
            <Card>
                <CardHeader>
                    <CardTitle class="text-sm font-medium"
                        >Site identity</CardTitle
                    >
                </CardHeader>
                <CardContent class="flex flex-col gap-4">
                    <div class="flex flex-col gap-2">
                        <Label for="site_url">Website URL</Label>
                        <Input
                            id="site_url"
                            v-model="form.site_url"
                            placeholder="https://example.com"
                            :disabled="!can.manage"
                        />
                        <p class="text-xs text-muted-foreground">
                            Used for canonical URLs and every sitemap entry.
                        </p>
                        <InputError :message="form.errors.site_url" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label for="separator">Title separator</Label>
                        <Input
                            id="separator"
                            v-model="form.seo_title_separator"
                            :disabled="!can.manage"
                        />
                        <InputError
                            :message="form.errors.seo_title_separator"
                        />
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label for="org">Organization name</Label>
                        <Input
                            id="org"
                            v-model="form.seo_organization_name"
                            placeholder="Solve CMS"
                            :disabled="!can.manage"
                        />
                        <p class="text-xs text-muted-foreground">
                            Used in Organization schema markup.
                        </p>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="text-sm font-medium"
                        >Crawling and verification</CardTitle
                    >
                </CardHeader>
                <CardContent class="flex flex-col gap-4">
                    <div class="flex flex-col gap-2">
                        <Label for="robots">Default robots directive</Label>
                        <Input
                            id="robots"
                            v-model="form.seo_default_robots"
                            placeholder="index,follow"
                            :disabled="!can.manage"
                        />
                        <InputError :message="form.errors.seo_default_robots" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label for="twitter">Twitter / X handle</Label>
                        <Input
                            id="twitter"
                            v-model="form.seo_twitter_handle"
                            placeholder="@solvecms"
                            :disabled="!can.manage"
                        />
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label for="verification"
                            >Google verification token</Label
                        >
                        <Input
                            id="verification"
                            v-model="form.seo_google_verification"
                            :disabled="!can.manage"
                        />
                    </div>

                    <label class="flex items-center gap-2">
                        <Checkbox
                            :model-value="form.seo_sitemap_enabled"
                            :disabled="!can.manage"
                            @update:model-value="
                                form.seo_sitemap_enabled = $event
                            "
                        />
                        <span class="text-sm">Generate sitemap.xml</span>
                    </label>
                </CardContent>
            </Card>

            <div v-if="can.manage" class="lg:col-span-2">
                <Button type="submit" :disabled="form.processing">
                    <Save class="size-4" />
                    Save settings
                </Button>
            </div>

            <p v-else class="text-sm text-muted-foreground lg:col-span-2">
                You have read-only access to SEO settings.
            </p>
        </form>
    </div>
</template>
