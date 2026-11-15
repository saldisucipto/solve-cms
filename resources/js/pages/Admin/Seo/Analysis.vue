<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ExternalLink } from '@lucide/vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { dashboard } from '@/routes/admin';
import {
    index as seoIndex,
    analysis as analysisRoute,
} from '@/routes/admin/seo';
import { statusLabel, statusVariant } from '@/lib/format';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'SEO', href: seoIndex() },
            { title: 'Content Analysis', href: analysisRoute() },
        ],
    },
});

const props = defineProps({
    items: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    issueTypes: { type: Object, default: () => ({}) },
});

function apply(type, issue) {
    router.get(
        analysisRoute(),
        { type, issue: issue === 'all' ? '' : issue },
        { preserveState: true, replace: true },
    );
}
</script>

<template>
    <Head title="SEO Content Analysis" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader
            title="Content Analysis"
            description="A checklist of content that needs SEO attention. These are recommendations, not ranking guarantees."
        />

        <Card>
            <CardContent class="flex flex-wrap gap-3 pt-4">
                <Select
                    :model-value="filters.type"
                    @update:model-value="apply($event, filters.issue)"
                >
                    <SelectTrigger class="w-36">
                        <SelectValue placeholder="All content" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All content</SelectItem>
                        <SelectItem value="post">Posts only</SelectItem>
                        <SelectItem value="page">Pages only</SelectItem>
                    </SelectContent>
                </Select>

                <Select
                    :model-value="filters.issue || 'all'"
                    @update:model-value="apply(filters.type, $event)"
                >
                    <SelectTrigger class="w-56">
                        <SelectValue placeholder="All issues" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All issues</SelectItem>
                        <SelectItem
                            v-for="(label, key) in issueTypes"
                            :key="key"
                            :value="key"
                        >
                            {{ label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </CardContent>
        </Card>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Title</TableHead>
                    <TableHead class="hidden sm:table-cell">Type</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead class="hidden md:table-cell">Words</TableHead>
                    <TableHead>Issues</TableHead>
                    <TableHead class="w-16"></TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow
                    v-for="item in items"
                    :key="`${item.type}-${item.id}`"
                >
                    <TableCell class="font-medium">{{ item.title }}</TableCell>
                    <TableCell
                        class="hidden text-xs capitalize sm:table-cell"
                        >{{ item.type }}</TableCell
                    >
                    <TableCell>
                        <Badge :variant="statusVariant(item.status)">{{
                            statusLabel(item.status)
                        }}</Badge>
                    </TableCell>
                    <TableCell class="hidden text-sm md:table-cell">{{
                        item.words
                    }}</TableCell>
                    <TableCell>
                        <div class="flex flex-wrap gap-1">
                            <Badge
                                v-for="key in item.issues"
                                :key="key"
                                variant="outline"
                                class="text-[10px]"
                            >
                                {{ issueTypes[key] ?? key }}
                            </Badge>
                            <span
                                v-if="!item.issues.length"
                                class="text-xs text-green-600"
                            >
                                No issues
                            </span>
                        </div>
                    </TableCell>
                    <TableCell>
                        <Button variant="ghost" size="icon" as-child>
                            <Link :href="item.edit_url" title="Edit">
                                <ExternalLink class="size-4" />
                            </Link>
                        </Button>
                    </TableCell>
                </TableRow>
                <TableRow v-if="!items.length">
                    <TableCell
                        colspan="6"
                        class="text-center text-muted-foreground"
                    >
                        Nothing matches these filters.
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
