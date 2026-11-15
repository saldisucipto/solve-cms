<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2, X } from '@lucide/vue';
import { ref } from 'vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input, InputError } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
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
} from '@/routes/admin/categories';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Categories', href: index() },
        ],
    },
});

const props = defineProps({
    categories: { type: Array, default: () => [] },
    parents: { type: Array, default: () => [] },
});

const editing = ref(null);

const form = useForm({
    name: '',
    slug: '',
    description: '',
    parent_id: null,
    order: 0,
});

function startCreate() {
    editing.value = null;
    form.reset();
}

function startEdit(cat) {
    editing.value = cat.id;
    form.name = cat.name;
    form.slug = cat.slug;
    form.description = cat.description ?? '';
    form.parent_id = cat.parent_id;
    form.order = cat.order;
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

function confirmDelete(cat) {
    if (confirm(`Delete category "${cat.name}"?`)) {
        router.delete(destroy(cat.id));
    }
}
</script>

<template>
    <Head title="Categories" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader
            title="Categories"
            description="Organise posts with hierarchical categories."
        />

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Form -->
            <Card class="h-fit">
                <CardHeader>
                    <CardTitle class="text-sm font-medium">
                        {{ editing ? 'Edit Category' : 'Add Category' }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <form class="flex flex-col gap-4" @submit.prevent="submit">
                        <div class="flex flex-col gap-2">
                            <Label>Name</Label>
                            <Input
                                v-model="form.name"
                                placeholder="Category name"
                            />
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

                        <div class="flex flex-col gap-2">
                            <Label>Parent</Label>
                            <Select
                                :model-value="
                                    form.parent_id === null
                                        ? 'none'
                                        : String(form.parent_id)
                                "
                                @update:model-value="
                                    form.parent_id =
                                        $event === 'none'
                                            ? null
                                            : Number($event)
                                "
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="No parent" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="none"
                                        >No parent</SelectItem
                                    >
                                    <SelectItem
                                        v-for="p in parents"
                                        :key="p.id"
                                        :value="p.id"
                                    >
                                        {{ p.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="flex gap-2">
                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="flex-1"
                            >
                                <Plus v-if="!editing" class="size-4" />
                                <Pencil v-else class="size-4" />
                                {{ editing ? 'Update' : 'Add Category' }}
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

            <!-- List -->
            <div class="lg:col-span-2">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Slug</TableHead>
                            <TableHead class="hidden sm:table-cell"
                                >Parent</TableHead
                            >
                            <TableHead class="hidden sm:table-cell"
                                >Posts</TableHead
                            >
                            <TableHead class="w-20">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="cat in categories" :key="cat.id">
                            <TableCell class="font-medium">{{
                                cat.name
                            }}</TableCell>
                            <TableCell class="text-xs text-muted-foreground">{{
                                cat.slug
                            }}</TableCell>
                            <TableCell
                                class="hidden text-xs text-muted-foreground sm:table-cell"
                            >
                                {{ cat.parent ?? '—' }}
                            </TableCell>
                            <TableCell class="hidden sm:table-cell">{{
                                cat.posts_count
                            }}</TableCell>
                            <TableCell>
                                <div class="flex gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="startEdit(cat)"
                                    >
                                        <Pencil class="size-3.5" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="text-destructive hover:text-destructive"
                                        @click="confirmDelete(cat)"
                                    >
                                        <Trash2 class="size-3.5" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!categories.length">
                            <TableCell
                                colspan="5"
                                class="text-center text-muted-foreground"
                            >
                                No categories yet.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </div>
</template>
