<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2, X } from '@lucide/vue';
import { ref } from 'vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input, InputError } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
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
    index,
    store,
    update as updateRoute,
    destroy,
} from '@/routes/admin/tags';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Tags', href: index() },
        ],
    },
});

const props = defineProps({
    tags: { type: Object, required: true },
});

const editing = ref(null);

const form = useForm({ name: '', slug: '', description: '' });

function startEdit(tag) {
    editing.value = tag.id;
    form.name = tag.name;
    form.slug = tag.slug;
    form.description = tag.description ?? '';
}

function cancelEdit() {
    editing.value = null;
    form.reset();
}

function submit() {
    if (editing.value) {
        form.put(updateRoute(editing.value), {
            onSuccess: () => {
                editing.value = null;
                form.reset();
            },
            preserveScroll: true,
        });
    } else {
        form.post(store(), {
            onSuccess: () => form.reset(),
            preserveScroll: true,
        });
    }
}

function confirmDelete(tag) {
    if (confirm(`Delete tag "${tag.name}"?`)) {
        router.delete(destroy(tag.id));
    }
}
</script>

<template>
    <Head title="Tags" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader
            title="Tags"
            description="Label posts with descriptive tags."
        />

        <div class="grid gap-6 lg:grid-cols-3">
            <Card class="h-fit">
                <CardHeader>
                    <CardTitle class="text-sm font-medium">
                        {{ editing ? 'Edit Tag' : 'Add Tag' }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <form class="flex flex-col gap-4" @submit.prevent="submit">
                        <div class="flex flex-col gap-2">
                            <Label>Name</Label>
                            <Input v-model="form.name" placeholder="Tag name" />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <Label>Slug</Label>
                            <Input
                                v-model="form.slug"
                                placeholder="auto-generated"
                            />
                        </div>
                        <div class="flex flex-col gap-2">
                            <Label>Description</Label>
                            <Textarea v-model="form.description" :rows="2" />
                        </div>
                        <div class="flex gap-2">
                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="flex-1"
                            >
                                <Plus v-if="!editing" class="size-4" />
                                <Pencil v-else class="size-4" />
                                {{ editing ? 'Update' : 'Add Tag' }}
                            </Button>
                            <Button
                                v-if="editing"
                                type="button"
                                variant="ghost"
                                @click="cancelEdit"
                            >
                                <X class="size-4" />
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <div class="lg:col-span-2">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Slug</TableHead>
                            <TableHead>Posts</TableHead>
                            <TableHead class="w-20">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="tag in tags.data" :key="tag.id">
                            <TableCell class="font-medium">{{
                                tag.name
                            }}</TableCell>
                            <TableCell class="text-xs text-muted-foreground">{{
                                tag.slug
                            }}</TableCell>
                            <TableCell>{{ tag.posts_count }}</TableCell>
                            <TableCell>
                                <div class="flex gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="startEdit(tag)"
                                    >
                                        <Pencil class="size-3.5" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="text-destructive hover:text-destructive"
                                        @click="confirmDelete(tag)"
                                    >
                                        <Trash2 class="size-3.5" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!tags.data?.length">
                            <TableCell
                                colspan="4"
                                class="text-center text-muted-foreground"
                            >
                                No tags yet.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </div>
</template>
