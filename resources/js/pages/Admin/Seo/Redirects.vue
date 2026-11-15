<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Power, Trash2, X } from '@lucide/vue';
import { ref } from 'vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input, InputError } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
import { index as seoIndex } from '@/routes/admin/seo';
import {
    index,
    store,
    update as updateRoute,
    destroy,
    toggle,
} from '@/routes/admin/seo/redirects';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'SEO', href: seoIndex() },
            { title: 'Redirects', href: index() },
        ],
    },
});

const props = defineProps({
    redirects: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statusCodes: { type: Array, default: () => [301, 302] },
});

const editing = ref(null);
const search = ref(props.filters.search ?? '');

const form = useForm({
    source: '',
    destination: '',
    status_code: 301,
    is_active: true,
});

function applySearch() {
    router.get(
        index(),
        { search: search.value },
        { preserveState: true, replace: true },
    );
}

function startEdit(redirect) {
    editing.value = redirect.id;
    form.source = redirect.source;
    form.destination = redirect.destination;
    form.status_code = redirect.status_code;
    form.is_active = redirect.is_active;
}

function reset() {
    editing.value = null;
    form.reset();
    form.status_code = 301;
    form.is_active = true;
}

function submit() {
    if (editing.value) {
        form.put(updateRoute(editing.value), {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    } else {
        form.post(store(), {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    }
}

function confirmDelete(redirect) {
    if (confirm(`Delete redirect from "${redirect.source}"?`)) {
        router.delete(destroy(redirect.id), { preserveScroll: true });
    }
}

function toggleActive(redirect) {
    router.patch(toggle(redirect.id), {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Redirect Manager" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader
            title="Redirect Manager"
            description="Send old URLs to new ones with 301 or 302 redirects."
        />

        <div class="grid gap-6 lg:grid-cols-3">
            <Card class="h-fit">
                <CardHeader>
                    <CardTitle class="text-sm font-medium">
                        {{ editing ? 'Edit Redirect' : 'Add Redirect' }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <form class="flex flex-col gap-4" @submit.prevent="submit">
                        <div class="flex flex-col gap-2">
                            <Label>From (path)</Label>
                            <Input
                                v-model="form.source"
                                placeholder="/old-page"
                            />
                            <InputError :message="form.errors.source" />
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label>To (path or URL)</Label>
                            <Input
                                v-model="form.destination"
                                placeholder="/new-page"
                            />
                            <InputError :message="form.errors.destination" />
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label>Type</Label>
                            <Select v-model="form.status_code">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="code in statusCodes"
                                        :key="code"
                                        :value="code"
                                    >
                                        {{ code
                                        }}{{
                                            code === 301
                                                ? ' — Permanent'
                                                : code === 302
                                                  ? ' — Temporary'
                                                  : ''
                                        }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <label class="flex items-center gap-2">
                            <Checkbox
                                :model-value="form.is_active"
                                @update:model-value="form.is_active = $event"
                            />
                            <span class="text-sm">Active</span>
                        </label>

                        <div class="flex gap-2">
                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="flex-1"
                            >
                                <Plus v-if="!editing" class="size-4" />
                                <Pencil v-else class="size-4" />
                                {{ editing ? 'Update' : 'Add Redirect' }}
                            </Button>
                            <Button
                                v-if="editing"
                                type="button"
                                variant="ghost"
                                @click="reset"
                            >
                                <X class="size-4" />
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <div class="flex flex-col gap-4 lg:col-span-2">
                <Card>
                    <CardContent class="flex gap-3 pt-4">
                        <Input
                            v-model="search"
                            placeholder="Search redirects…"
                            class="w-64"
                            @keydown.enter="applySearch"
                        />
                        <Button variant="secondary" @click="applySearch"
                            >Search</Button
                        >
                    </CardContent>
                </Card>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>From</TableHead>
                            <TableHead>To</TableHead>
                            <TableHead class="hidden sm:table-cell"
                                >Code</TableHead
                            >
                            <TableHead class="hidden md:table-cell"
                                >Hits</TableHead
                            >
                            <TableHead class="w-28">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="redirect in redirects.data"
                            :key="redirect.id"
                        >
                            <TableCell class="max-w-[180px] truncate text-xs">{{
                                redirect.source
                            }}</TableCell>
                            <TableCell
                                class="max-w-[180px] truncate text-xs text-muted-foreground"
                            >
                                {{ redirect.destination }}
                            </TableCell>
                            <TableCell class="hidden sm:table-cell">
                                <Badge
                                    :variant="
                                        redirect.is_active
                                            ? 'default'
                                            : 'secondary'
                                    "
                                >
                                    {{ redirect.status_code }}
                                </Badge>
                            </TableCell>
                            <TableCell class="hidden text-xs md:table-cell">{{
                                redirect.hits
                            }}</TableCell>
                            <TableCell>
                                <div class="flex gap-0.5">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        :title="
                                            redirect.is_active
                                                ? 'Disable'
                                                : 'Enable'
                                        "
                                        @click="toggleActive(redirect)"
                                    >
                                        <Power
                                            class="size-3.5"
                                            :class="
                                                redirect.is_active
                                                    ? 'text-green-600'
                                                    : 'text-muted-foreground'
                                            "
                                        />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="startEdit(redirect)"
                                    >
                                        <Pencil class="size-3.5" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="text-destructive hover:text-destructive"
                                        @click="confirmDelete(redirect)"
                                    >
                                        <Trash2 class="size-3.5" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!redirects.data.length">
                            <TableCell
                                colspan="5"
                                class="text-center text-muted-foreground"
                            >
                                No redirects yet.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <div
                    v-if="redirects.last_page > 1"
                    class="flex items-center justify-between text-sm text-muted-foreground"
                >
                    <span
                        >Showing {{ redirects.from }}–{{ redirects.to }} of
                        {{ redirects.total }}</span
                    >
                    <div class="flex gap-1">
                        <Button
                            v-for="link in redirects.links"
                            :key="link.label"
                            :variant="link.active ? 'default' : 'ghost'"
                            size="sm"
                            :disabled="!link.url"
                            as-child
                        >
                            <a
                                v-if="link.url"
                                :href="link.url"
                                v-html="link.label"
                            />
                            <span v-else v-html="link.label" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
