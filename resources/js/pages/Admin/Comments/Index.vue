<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
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
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { InputError } from '@/components/ui/input';
import { dashboard } from '@/routes/admin';
import {
    index,
    reply as replyRoute,
    status as updateStatus,
    destroy,
} from '@/routes/admin/comments';
import { statusLabel, statusVariant } from '@/lib/format';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Comments', href: index() },
        ],
    },
});

const props = defineProps({
    comments: { type: Object, required: true },
    counts: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const replyingTo = ref(null);
const replyForm = useForm({ content: '' });

function applyFilters() {
    router.get(
        index(),
        { search: search.value, status: status.value },
        { preserveState: true, replace: true },
    );
}

function setStatus(comment, newStatus) {
    router.patch(
        updateStatus(comment.id),
        { status: newStatus },
        { preserveScroll: true },
    );
}

function startReply(comment) {
    replyingTo.value = comment.id;
    replyForm.reset();
}

function submitReply(comment) {
    replyForm.post(replyRoute(comment.id), {
        onSuccess: () => {
            replyingTo.value = null;
            replyForm.reset();
        },
        preserveScroll: true,
    });
}

function confirmDelete(comment) {
    if (confirm('Delete this comment?')) {
        router.delete(destroy(comment.id));
    }
}
</script>

<template>
    <Head title="Comments" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader title="Comments" description="Moderate visitor comments." />

        <!-- Status tabs -->
        <div class="flex flex-wrap gap-2">
            <Button
                v-for="(count, s) in counts"
                :key="s"
                :variant="
                    filters.status === s || (!filters.status && s === 'all')
                        ? 'default'
                        : 'outline'
                "
                size="sm"
                @click="
                    status = s === 'all' ? '' : s;
                    applyFilters();
                "
            >
                {{ statusLabel(s) }}
                <Badge variant="secondary" class="ml-1.5">{{ count }}</Badge>
            </Button>
        </div>

        <Card>
            <CardContent class="flex flex-wrap gap-3 pt-4">
                <Input
                    v-model="search"
                    placeholder="Search comments…"
                    class="w-64"
                    @keydown.enter="applyFilters"
                />
                <Button variant="secondary" @click="applyFilters"
                    >Search</Button
                >
            </CardContent>
        </Card>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Author</TableHead>
                    <TableHead>Comment</TableHead>
                    <TableHead class="hidden md:table-cell">Post</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead class="w-32">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <template v-for="comment in comments.data" :key="comment.id">
                    <TableRow>
                        <TableCell class="align-top">
                            <div class="font-medium">
                                {{ comment.author_name }}
                            </div>
                            <div class="text-xs text-muted-foreground">
                                {{ comment.author_email }}
                            </div>
                        </TableCell>
                        <TableCell class="align-top">
                            <p class="text-sm">{{ comment.content }}</p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ comment.created_at }}
                            </p>

                            <!-- Reply form -->
                            <div v-if="replyingTo === comment.id" class="mt-3">
                                <Textarea
                                    v-model="replyForm.content"
                                    :rows="3"
                                    placeholder="Write a reply…"
                                />
                                <InputError
                                    :message="replyForm.errors.content"
                                />
                                <div class="mt-2 flex gap-2">
                                    <Button
                                        size="sm"
                                        @click="submitReply(comment)"
                                        >Send Reply</Button
                                    >
                                    <Button
                                        size="sm"
                                        variant="ghost"
                                        @click="replyingTo = null"
                                        >Cancel</Button
                                    >
                                </div>
                            </div>

                            <!-- Existing replies -->
                            <div
                                v-if="comment.replies?.length"
                                class="mt-2 space-y-2 border-l-2 border-muted pl-3"
                            >
                                <div
                                    v-for="reply in comment.replies"
                                    :key="reply.id"
                                    class="text-xs"
                                >
                                    <span class="font-medium">{{
                                        reply.author_name
                                    }}</span
                                    >:
                                    {{ reply.content }}
                                </div>
                            </div>
                        </TableCell>
                        <TableCell
                            class="hidden align-top text-xs text-muted-foreground md:table-cell"
                        >
                            {{ comment.post?.title ?? '—' }}
                        </TableCell>
                        <TableCell class="align-top">
                            <Badge :variant="statusVariant(comment.status)">
                                {{ statusLabel(comment.status) }}
                            </Badge>
                        </TableCell>
                        <TableCell class="align-top">
                            <div class="flex flex-col gap-1">
                                <Button
                                    v-if="comment.status !== 'approved'"
                                    variant="ghost"
                                    size="sm"
                                    class="text-green-600 hover:text-green-700"
                                    @click="setStatus(comment, 'approved')"
                                >
                                    Approve
                                </Button>
                                <Button
                                    v-if="comment.status !== 'spam'"
                                    variant="ghost"
                                    size="sm"
                                    @click="setStatus(comment, 'spam')"
                                >
                                    Spam
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="startReply(comment)"
                                >
                                    Reply
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="text-destructive hover:text-destructive"
                                    @click="confirmDelete(comment)"
                                >
                                    Delete
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </template>
                <TableRow v-if="!comments.data.length">
                    <TableCell
                        colspan="5"
                        class="text-center text-muted-foreground"
                    >
                        No comments found.
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
