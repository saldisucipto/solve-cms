<script setup>
import { Head, Link } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ExternalLink,
    FileSearch,
    Link2,
    ShieldCheck,
} from '@lucide/vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { dashboard } from '@/routes/admin';
import { index as seoIndex, analysis, settings } from '@/routes/admin/seo';
import { index as redirects } from '@/routes/admin/seo/redirects';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'SEO', href: seoIndex() },
        ],
    },
});

defineProps({
    stats: { type: Object, default: () => ({}) },
    urls: { type: Object, default: () => ({}) },
    sitemapEnabled: { type: Boolean, default: true },
    recentRedirects: { type: Array, default: () => [] },
});
</script>

<template>
    <Head title="SEO Overview" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader
            title="SEO Overview"
            description="Technical SEO status for this website."
        >
            <template #actions>
                <Button variant="outline" as-child>
                    <Link :href="settings()">SEO Settings</Link>
                </Button>
                <Button as-child>
                    <Link :href="analysis()">
                        <FileSearch class="size-4" />
                        Content Analysis
                    </Link>
                </Button>
            </template>
        </PageHeader>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground"
                        >Content entries</CardTitle
                    >
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold">{{ stats.content }}</p>
                    <p class="mt-0.5 text-xs text-muted-foreground">
                        Posts and pages combined
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle
                        class="flex items-center gap-1.5 text-sm font-medium text-muted-foreground"
                    >
                        <AlertTriangle class="size-3.5" /> Missing SEO title
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p
                        class="text-2xl font-bold"
                        :class="stats.missing_title ? 'text-amber-600' : ''"
                    >
                        {{ stats.missing_title }}
                    </p>
                    <p class="mt-0.5 text-xs text-muted-foreground">
                        {{ stats.missing_description }} missing descriptions
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle
                        class="flex items-center gap-1.5 text-sm font-medium text-muted-foreground"
                    >
                        <ShieldCheck class="size-3.5" /> Missing focus keyword
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p
                        class="text-2xl font-bold"
                        :class="stats.missing_keyword ? 'text-amber-600' : ''"
                    >
                        {{ stats.missing_keyword }}
                    </p>
                    <p class="mt-0.5 text-xs text-muted-foreground">
                        Entries without a target term
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle
                        class="flex items-center gap-1.5 text-sm font-medium text-muted-foreground"
                    >
                        <Link2 class="size-3.5" /> Redirects
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold">{{ stats.redirects }}</p>
                    <p class="mt-0.5 text-xs text-muted-foreground">
                        {{ stats.active_redirects }} active
                    </p>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle class="text-sm font-medium"
                        >Technical endpoints</CardTitle
                    >
                </CardHeader>
                <CardContent class="flex flex-col gap-3">
                    <div
                        class="flex items-center justify-between rounded-md border px-3 py-2"
                    >
                        <div>
                            <p class="text-sm font-medium">sitemap.xml</p>
                            <p class="text-xs text-muted-foreground">
                                {{ urls.sitemap }}
                            </p>
                        </div>
                        <Button variant="ghost" size="sm" as-child>
                            <a
                                :href="urls.sitemap"
                                target="_blank"
                                rel="noopener"
                            >
                                <ExternalLink class="size-4" />
                            </a>
                        </Button>
                    </div>

                    <div
                        class="flex items-center justify-between rounded-md border px-3 py-2"
                    >
                        <div>
                            <p class="text-sm font-medium">robots.txt</p>
                            <p class="text-xs text-muted-foreground">
                                {{ urls.robots }}
                            </p>
                        </div>
                        <Button variant="ghost" size="sm" as-child>
                            <a
                                :href="urls.robots"
                                target="_blank"
                                rel="noopener"
                            >
                                <ExternalLink class="size-4" />
                            </a>
                        </Button>
                    </div>

                    <div
                        class="flex items-center justify-between rounded-md border px-3 py-2"
                    >
                        <p class="text-sm font-medium">Sitemap generation</p>
                        <Badge
                            :variant="sitemapEnabled ? 'default' : 'secondary'"
                        >
                            {{ sitemapEnabled ? 'Enabled' : 'Disabled' }}
                        </Badge>
                    </div>

                    <p class="text-xs text-muted-foreground">
                        The sitemap lists the homepage plus every public entry
                        whose route is registered. As the public frontend ships,
                        its pages join the sitemap automatically.
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle class="text-sm font-medium"
                        >Recent redirects</CardTitle
                    >
                    <Button variant="ghost" size="sm" as-child>
                        <Link :href="redirects()">Manage</Link>
                    </Button>
                </CardHeader>
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>From</TableHead>
                                <TableHead>To</TableHead>
                                <TableHead class="text-right">Hits</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="redirect in recentRedirects"
                                :key="redirect.id"
                            >
                                <TableCell
                                    class="max-w-[160px] truncate text-xs"
                                    >{{ redirect.source }}</TableCell
                                >
                                <TableCell
                                    class="max-w-[160px] truncate text-xs text-muted-foreground"
                                >
                                    {{ redirect.destination }}
                                </TableCell>
                                <TableCell class="text-right text-xs">{{
                                    redirect.hits
                                }}</TableCell>
                            </TableRow>
                            <TableRow v-if="!recentRedirects.length">
                                <TableCell
                                    colspan="3"
                                    class="text-center text-sm text-muted-foreground"
                                >
                                    No redirects yet.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
