<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
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
import { index, create, edit, destroy } from '@/routes/admin/pages';
import { statusLabel, statusVariant, formatDate } from '@/lib/format';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Pages', href: index() },
        ],
    },
});

const props = defineProps({
    pages: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    can: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status || 'all');

function applyFilters() {
    router.get(
        index(),
        { search: search.value, status: status.value },
        { preserveState: true, replace: true },
    );
}

function confirmDelete(page) {
    if (confirm(`Delete "${page.title}"?`)) {
        router.delete(destroy(page.id));
    }
}
</script>

<template>
    <Head title="Pages" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader
            title="Pages"
            description="Manage static pages on your website."
        >
            <template #actions>
                <Button v-if="can.create" as-child>
                    <Link :href="create()">
                        <Plus class="size-4" />
                        New Page
                    </Link>
                </Button>
            </template>
        </PageHeader>

        <Card>
            <CardContent class="flex flex-wrap gap-3 pt-4">
                <Input
                    v-model="search"
                    placeholder="Search pages…"
                    class="w-56"
                    @keydown.enter="applyFilters"
                />
                <Select v-model="status" @update:model-value="applyFilters">
                    <SelectTrigger class="w-36">
                        <SelectValue placeholder="All statuses" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All statuses</SelectItem>
                        <SelectItem value="published">Published</SelectItem>
                        <SelectItem value="draft">Draft</SelectItem>
                    </SelectContent>
                </Select>
                <Button variant="secondary" @click="applyFilters"
                    >Search</Button
                >
            </CardContent>
        </Card>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Title</TableHead>
                    <TableHead class="hidden md:table-cell">Template</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead class="hidden md:table-cell">Parent</TableHead>
                    <TableHead class="hidden lg:table-cell">Updated</TableHead>
                    <TableHead class="w-24">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="page in pages.data" :key="page.id">
                    <TableCell class="font-medium">{{ page.title }}</TableCell>
                    <TableCell
                        class="hidden text-xs text-muted-foreground md:table-cell"
                    >
                        {{ page.template }}
                    </TableCell>
                    <TableCell>
                        <Badge :variant="statusVariant(page.status)">
                            {{ statusLabel(page.status) }}
                        </Badge>
                    </TableCell>
                    <TableCell
                        class="hidden text-xs text-muted-foreground md:table-cell"
                    >
                        {{ page.parent ?? '—' }}
                    </TableCell>
                    <TableCell
                        class="hidden text-xs text-muted-foreground lg:table-cell"
                    >
                        {{ formatDate(page.updated_at) }}
                    </TableCell>
                    <TableCell>
                        <div class="flex gap-1">
                            <Button variant="ghost" size="sm" as-child>
                                <Link :href="edit(page.id)">Edit</Link>
                            </Button>
                            <Button
                                variant="ghost"
                                size="sm"
                                class="text-destructive hover:text-destructive"
                                @click="confirmDelete(page)"
                            >
                                Delete
                            </Button>
                        </div>
                    </TableCell>
                </TableRow>
                <TableRow v-if="!pages.data.length">
                    <TableCell
                        colspan="6"
                        class="text-center text-muted-foreground"
                    >
                        No pages found.
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
