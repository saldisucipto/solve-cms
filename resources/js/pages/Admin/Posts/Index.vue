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
import { index, create, edit, destroy } from '@/routes/admin/posts';
import { statusLabel, statusVariant, formatDate } from '@/lib/format';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Posts', href: index() },
        ],
    },
});

const props = defineProps({
    posts: { type: Object, required: true },
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

function confirmDelete(post) {
    if (confirm(`Delete "${post.title}"? This cannot be undone.`)) {
        router.delete(destroy(post.id));
    }
}
</script>

<template>
    <Head title="Posts" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader
            title="Posts"
            description="Create and manage your blog posts."
        >
            <template #actions>
                <Button v-if="can.create" as-child>
                    <Link :href="create()">
                        <Plus class="size-4" />
                        New Post
                    </Link>
                </Button>
            </template>
        </PageHeader>

        <!-- Filters -->
        <Card>
            <CardContent class="flex flex-wrap gap-3 pt-4">
                <Input
                    v-model="search"
                    placeholder="Search posts…"
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
                        <SelectItem value="pending">Pending</SelectItem>
                        <SelectItem value="scheduled">Scheduled</SelectItem>
                    </SelectContent>
                </Select>
                <Button variant="secondary" @click="applyFilters"
                    >Search</Button
                >
            </CardContent>
        </Card>

        <!-- Table -->
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Title</TableHead>
                    <TableHead class="hidden md:table-cell"
                        >Categories</TableHead
                    >
                    <TableHead>Status</TableHead>
                    <TableHead class="hidden sm:table-cell">Author</TableHead>
                    <TableHead class="hidden lg:table-cell">Updated</TableHead>
                    <TableHead class="w-24">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="post in posts.data" :key="post.id">
                    <TableCell class="font-medium">{{ post.title }}</TableCell>
                    <TableCell class="hidden md:table-cell">
                        <span class="text-xs text-muted-foreground">
                            {{ post.categories?.join(', ') || '—' }}
                        </span>
                    </TableCell>
                    <TableCell>
                        <Badge :variant="statusVariant(post.status)">
                            {{ statusLabel(post.status) }}
                        </Badge>
                    </TableCell>
                    <TableCell class="hidden text-sm sm:table-cell">
                        {{ post.author ?? '—' }}
                    </TableCell>
                    <TableCell
                        class="hidden text-xs text-muted-foreground lg:table-cell"
                    >
                        {{ formatDate(post.updated_at) }}
                    </TableCell>
                    <TableCell>
                        <div class="flex gap-1">
                            <Button variant="ghost" size="sm" as-child>
                                <Link :href="edit(post.id)">Edit</Link>
                            </Button>
                            <Button
                                variant="ghost"
                                size="sm"
                                class="text-destructive hover:text-destructive"
                                @click="confirmDelete(post)"
                            >
                                Delete
                            </Button>
                        </div>
                    </TableCell>
                </TableRow>
                <TableRow v-if="!posts.data.length">
                    <TableCell
                        colspan="6"
                        class="text-center text-muted-foreground"
                    >
                        No posts found.
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <!-- Pagination -->
        <div
            v-if="posts.last_page > 1"
            class="flex items-center justify-between text-sm text-muted-foreground"
        >
            <span
                >Showing {{ posts.from }}–{{ posts.to }} of
                {{ posts.total }}</span
            >
            <div class="flex gap-1">
                <Button
                    v-for="link in posts.links"
                    :key="link.label"
                    :variant="link.active ? 'default' : 'ghost'"
                    size="sm"
                    :disabled="!link.url"
                    as-child
                >
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        v-html="link.label"
                    />
                    <span v-else v-html="link.label" />
                </Button>
            </div>
        </div>
    </div>
</template>
