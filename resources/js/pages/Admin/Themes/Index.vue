<script setup>
import { Head, router } from '@inertiajs/vue3';
import { CheckCircle2, ExternalLink, Palette } from '@lucide/vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes/admin';
import { index, activate, deactivate } from '@/routes/admin/themes';
import { index as customizer } from '@/routes/admin/customizer';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Themes', href: index() },
        ],
    },
});

const props = defineProps({
    themes: { type: Array, default: () => [] },
    can: { type: Object, default: () => ({}) },
    previewUrl: { type: String, default: '' },
});

function activateTheme(slug) {
    if (
        confirm('Activate this theme? The public site will use it immediately.')
    ) {
        router.post(activate(slug), {}, { preserveScroll: true });
    }
}

function deactivateTheme(slug) {
    if (
        confirm('Deactivate this theme? The site will fall back to defaults.')
    ) {
        router.post(deactivate(slug), {}, { preserveScroll: true });
    }
}

function templateSummary(theme) {
    const templates = theme.templates ?? {};
    return Object.entries(templates)
        .map(
            ([key, options]) =>
                `${key}: ${Array.isArray(options) ? options.length : 0}`,
        )
        .join(' · ');
}
</script>

<template>
    <Head title="Themes" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader
            title="Themes"
            description="Install-free themes shipped with Solve CMS. Activate one to change the public site."
        >
            <template #actions>
                <Button v-if="can.customize" as-child variant="outline">
                    <a :href="previewUrl" target="_blank" rel="noopener">
                        <ExternalLink class="size-4" />
                        Preview site
                    </a>
                </Button>
                <Button v-if="can.customize" as-child>
                    <a :href="customizer().url">
                        <Palette class="size-4" />
                        Customize
                    </a>
                </Button>
            </template>
        </PageHeader>

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            <Card
                v-for="theme in themes"
                :key="theme.slug"
                class="flex flex-col"
                :class="theme.is_active ? 'ring-2 ring-primary' : ''"
            >
                <div
                    class="flex h-32 items-center justify-center rounded-t-xl bg-gradient-to-br from-slate-100 to-slate-200 text-4xl font-bold text-slate-400 dark:from-slate-800 dark:to-slate-900"
                >
                    {{ theme.name.charAt(0) }}
                </div>

                <CardHeader class="pb-2">
                    <div class="flex items-start justify-between gap-2">
                        <CardTitle class="text-base">{{
                            theme.name
                        }}</CardTitle>
                        <Badge
                            v-if="theme.is_active"
                            variant="default"
                            class="gap-1"
                        >
                            <CheckCircle2 class="size-3" />
                            Active
                        </Badge>
                    </div>
                    <p class="text-xs text-muted-foreground">
                        v{{ theme.version
                        }}<span v-if="theme.author"> · {{ theme.author }}</span>
                    </p>
                </CardHeader>

                <CardContent class="flex flex-1 flex-col gap-3">
                    <p class="text-sm text-muted-foreground">
                        {{ theme.description }}
                    </p>

                    <div class="flex flex-wrap gap-1.5 text-[11px]">
                        <Badge v-if="theme.has_stylesheet" variant="outline"
                            >Custom CSS</Badge
                        >
                        <Badge v-if="theme.has_draft" variant="secondary"
                            >Unpublished draft</Badge
                        >
                        <Badge
                            v-for="key in Object.keys(theme.templates ?? {})"
                            :key="key"
                            variant="outline"
                        >
                            {{ key }}
                        </Badge>
                    </div>

                    <p class="text-[11px] text-muted-foreground">
                        {{ templateSummary(theme) }}
                    </p>

                    <div class="mt-auto flex gap-2 pt-2">
                        <Button
                            v-if="!theme.is_active && can.manage"
                            size="sm"
                            @click="activateTheme(theme.slug)"
                        >
                            Activate
                        </Button>
                        <Button
                            v-if="theme.is_active && can.manage"
                            size="sm"
                            variant="outline"
                            @click="deactivateTheme(theme.slug)"
                        >
                            Deactivate
                        </Button>
                        <Button
                            v-if="can.customize"
                            size="sm"
                            variant="ghost"
                            as-child
                        >
                            <a :href="customizer().url">Customize</a>
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <p v-if="!themes.length" class="text-sm text-muted-foreground">
                No themes found in <code>resources/themes</code>.
            </p>
        </div>
    </div>
</template>
