<script setup>
import { Head } from '@inertiajs/vue3';
import { ExternalLink, FileText } from '@lucide/vue';
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
import { index as seoIndex } from '@/routes/admin/seo';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'SEO', href: seoIndex() },
            { title: 'Sitemap' },
        ],
    },
});

const props = defineProps({
    enabled: { type: Boolean, default: true },
    url: { type: String, default: '' },
    robotsUrl: { type: String, default: '' },
    counts: { type: Object, default: () => ({}) },
    total: { type: Number, default: 0 },
    entries: { type: Array, default: () => [] },
    truncated: { type: Boolean, default: false },
});

const TYPE_LABELS = {
    home: 'Homepage',
    page: 'Pages',
    post: 'Posts',
    category: 'Categories',
    tag: 'Tags',
};

function typeBadge(name) {
    return name === 'home' ? 'default' : 'secondary';
}
</script>

<template>
    <Head title="Sitemap" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader
            title="Sitemap"
            description="Exactly what crawlers receive at /sitemap.xml."
        >
            <template #actions>
                <Button variant="outline" as-child>
                    <a :href="robotsUrl" target="_blank" rel="noopener">
                        <FileText class="size-4" />
                        robots.txt
                    </a>
                </Button>
                <Button as-child>
                    <a :href="url" target="_blank" rel="noopener">
                        <ExternalLink class="size-4" />
                        Open sitemap.xml
                    </a>
                </Button>
            </template>
        </PageHeader>

        <div
            v-if="!enabled"
            class="rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900"
        >
            Sitemap generation is switched off. Enable it in
            <a href="/admin/seo/settings" class="underline">SEO Settings</a> —
            until then <code>/sitemap.xml</code> returns 404.
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground"
                        >Total URLs</CardTitle
                    >
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold">{{ total }}</p>
                </CardContent>
            </Card>

            <Card v-for="(count, type) in counts" :key="type">
                <CardHeader class="pb-2">
                    <CardTitle
                        class="text-sm font-medium text-muted-foreground"
                    >
                        {{ TYPE_LABELS[type] ?? type }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold">{{ count }}</p>
                </CardContent>
            </Card>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead class="w-28">Type</TableHead>
                    <TableHead>URL</TableHead>
                    <TableHead class="hidden lg:table-cell"
                        >Last modified</TableHead
                    >
                    <TableHead class="hidden sm:table-cell">Change</TableHead>
                    <TableHead class="hidden sm:table-cell">Priority</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="entry in entries" :key="entry.loc">
                    <TableCell>
                        <Badge
                            :variant="typeBadge(entry.type)"
                            class="text-[10px]"
                        >
                            {{ entry.type }}
                        </Badge>
                    </TableCell>
                    <TableCell class="max-w-[420px] truncate text-xs">
                        <a
                            :href="entry.loc"
                            target="_blank"
                            rel="noopener"
                            class="hover:underline"
                        >
                            {{ entry.loc }}
                        </a>
                    </TableCell>
                    <TableCell
                        class="hidden text-xs text-muted-foreground lg:table-cell"
                    >
                        {{ entry.lastmod ?? '—' }}
                    </TableCell>
                    <TableCell
                        class="hidden text-xs text-muted-foreground sm:table-cell"
                    >
                        {{ entry.changefreq }}
                    </TableCell>
                    <TableCell
                        class="hidden text-xs text-muted-foreground sm:table-cell"
                    >
                        {{ entry.priority }}
                    </TableCell>
                </TableRow>

                <TableRow v-if="!entries.length">
                    <TableCell
                        colspan="5"
                        class="py-10 text-center text-muted-foreground"
                    >
                        No URLs in the sitemap yet.
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <p v-if="truncated" class="text-xs text-muted-foreground">
            Showing the first 200 entries. The XML file contains all of them.
        </p>
    </div>
</template>
