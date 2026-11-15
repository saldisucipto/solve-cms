<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowUpRight,
    FileText,
    FolderTree,
    Image,
    Layers,
    MessageSquare,
    Users,
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
import { index as postsIndex } from '@/routes/admin/posts';
import { index as commentsIndex } from '@/routes/admin/comments';
import { statusLabel, statusVariant } from '@/lib/format';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
    },
});

const props = defineProps({
    stats: { type: Array, default: () => [] },
    recentPosts: { type: Array, default: () => [] },
    pendingComments: { type: Array, default: () => [] },
    contentByStatus: { type: Object, default: () => ({}) },
    publishingActivity: { type: Array, default: () => [] },
});

const ICONS = {
    'file-text': FileText,
    layers: Layers,
    image: Image,
    'message-square': MessageSquare,
    'folder-tree': FolderTree,
    users: Users,
};

const visibleStats = computed(() =>
    props.stats.filter((s) => s.value !== null),
);

const activityMax = computed(() =>
    Math.max(1, ...props.publishingActivity.map((d) => d.count)),
);
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader
            title="Dashboard"
            description="Overview of your Solve CMS content."
        />

        <!-- Stat cards -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Card
                v-for="stat in visibleStats"
                :key="stat.label"
                class="transition-shadow hover:shadow-md"
            >
                <CardHeader
                    class="flex flex-row items-center justify-between pb-2"
                >
                    <CardTitle
                        class="text-sm font-medium text-muted-foreground"
                    >
                        {{ stat.label }}
                    </CardTitle>
                    <component
                        :is="ICONS[stat.icon]"
                        class="size-4 text-muted-foreground"
                    />
                </CardHeader>
                <CardContent>
                    <div class="flex items-end justify-between">
                        <div>
                            <p class="text-2xl font-bold">{{ stat.value }}</p>
                            <p
                                v-if="stat.detail"
                                class="mt-0.5 text-xs text-muted-foreground"
                            >
                                {{ stat.detail }}
                            </p>
                        </div>
                        <Button
                            v-if="stat.href"
                            variant="ghost"
                            size="icon"
                            class="size-8"
                            as-child
                        >
                            <Link :href="stat.href">
                                <ArrowUpRight class="size-4" />
                            </Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Publishing activity sparkline -->
        <Card v-if="publishingActivity.length">
            <CardHeader>
                <CardTitle class="text-sm font-medium"
                    >Publishing Activity — Last 14 Days</CardTitle
                >
            </CardHeader>
            <CardContent>
                <div class="flex h-16 items-end gap-1">
                    <div
                        v-for="day in publishingActivity"
                        :key="day.date"
                        class="group relative flex flex-1 flex-col items-center"
                    >
                        <div
                            class="w-full rounded-sm bg-blue-500 dark:bg-blue-400"
                            :style="{
                                height: `${Math.max(4, (day.count / activityMax) * 100)}%`,
                                opacity: day.count === 0 ? 0.2 : 1,
                            }"
                        />
                        <span
                            class="absolute bottom-full mb-1 hidden rounded bg-foreground px-1.5 py-0.5 text-[10px] text-background group-hover:block"
                        >
                            {{ day.label }}: {{ day.count }}
                        </span>
                    </div>
                </div>
            </CardContent>
        </Card>

        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Recent posts -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle class="text-sm font-medium"
                        >Recent Posts</CardTitle
                    >
                    <Button variant="ghost" size="sm" as-child>
                        <Link :href="postsIndex()">View all</Link>
                    </Button>
                </CardHeader>
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Title</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="hidden sm:table-cell"
                                    >Updated</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="post in recentPosts"
                                :key="post.id"
                            >
                                <TableCell
                                    class="max-w-[180px] truncate font-medium"
                                >
                                    {{ post.title }}
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="statusVariant(post.status)"
                                    >
                                        {{ statusLabel(post.status) }}
                                    </Badge>
                                </TableCell>
                                <TableCell
                                    class="hidden text-xs text-muted-foreground sm:table-cell"
                                >
                                    {{ post.updated_at }}
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="!recentPosts.length">
                                <TableCell
                                    colspan="3"
                                    class="text-center text-sm text-muted-foreground"
                                >
                                    No posts yet.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <!-- Pending comments -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle class="text-sm font-medium"
                        >Pending Comments</CardTitle
                    >
                    <Button variant="ghost" size="sm" as-child>
                        <Link :href="commentsIndex()">View all</Link>
                    </Button>
                </CardHeader>
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Author</TableHead>
                                <TableHead>Excerpt</TableHead>
                                <TableHead class="hidden sm:table-cell"
                                    >Post</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="comment in pendingComments"
                                :key="comment.id"
                            >
                                <TableCell class="font-medium">
                                    {{ comment.author_name }}
                                </TableCell>
                                <TableCell
                                    class="max-w-[160px] truncate text-xs text-muted-foreground"
                                >
                                    {{ comment.content }}
                                </TableCell>
                                <TableCell
                                    class="hidden max-w-[120px] truncate text-xs text-muted-foreground sm:table-cell"
                                >
                                    {{ comment.post }}
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="!pendingComments.length">
                                <TableCell
                                    colspan="3"
                                    class="text-center text-sm text-muted-foreground"
                                >
                                    No pending comments.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>

        <!-- Content by status -->
        <Card>
            <CardHeader>
                <CardTitle class="text-sm font-medium"
                    >Content by Status</CardTitle
                >
            </CardHeader>
            <CardContent class="flex flex-wrap gap-4">
                <div
                    v-for="(count, status) in contentByStatus"
                    :key="status"
                    class="flex items-center gap-2"
                >
                    <Badge :variant="statusVariant(status)">{{
                        statusLabel(status)
                    }}</Badge>
                    <span class="text-sm font-medium">{{ count }}</span>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
