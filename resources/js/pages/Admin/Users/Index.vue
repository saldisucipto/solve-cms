<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
    update as updateRoute,
    status as statusRoute,
} from '@/routes/admin/users';
import { formatDate, statusVariant } from '@/lib/format';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Users', href: index() },
        ],
    },
});

const props = defineProps({
    users: { type: Object, required: true },
    roles: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    can: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');
const editing = ref(null);
const editName = ref('');
const editStatus = ref('active');
const editRoles = ref([]);

function applyFilters() {
    router.get(
        index(),
        { search: search.value },
        { preserveState: true, replace: true },
    );
}

function startEdit(user) {
    editing.value = user.id;
    editName.value = user.name;
    editStatus.value = user.status;
    editRoles.value = [...user.role_ids];
}

function toggleRole(id) {
    const i = editRoles.value.indexOf(id);
    if (i === -1) editRoles.value.push(id);
    else editRoles.value.splice(i, 1);
}

function save() {
    router.put(
        updateRoute(editing.value),
        {
            name: editName.value,
            status: editStatus.value,
            roles: editRoles.value,
        },
        { preserveScroll: true, onSuccess: () => (editing.value = null) },
    );
}

function toggleStatus(user) {
    router.patch(statusRoute(user.id), {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Users" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader
            title="Users"
            description="Manage accounts, roles and access."
        />

        <Card>
            <CardContent class="flex flex-wrap gap-3 pt-4">
                <Input
                    v-model="search"
                    placeholder="Search users…"
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
                    <TableHead>Name</TableHead>
                    <TableHead class="hidden md:table-cell">Email</TableHead>
                    <TableHead>Roles</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead class="hidden lg:table-cell"
                        >Last login</TableHead
                    >
                    <TableHead class="w-24">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <template v-for="user in users.data" :key="user.id">
                    <TableRow>
                        <TableCell class="font-medium">{{
                            user.name
                        }}</TableCell>
                        <TableCell
                            class="hidden text-sm text-muted-foreground md:table-cell"
                        >
                            {{ user.email }}
                        </TableCell>
                        <TableCell>
                            <div class="flex flex-wrap gap-1">
                                <Badge
                                    v-for="role in user.roles"
                                    :key="role"
                                    variant="secondary"
                                >
                                    {{ role }}
                                </Badge>
                                <span
                                    v-if="!user.roles.length"
                                    class="text-xs text-muted-foreground"
                                    >—</span
                                >
                            </div>
                        </TableCell>
                        <TableCell>
                            <Badge :variant="statusVariant(user.status)">
                                {{ user.status }}
                            </Badge>
                        </TableCell>
                        <TableCell
                            class="hidden text-xs text-muted-foreground lg:table-cell"
                        >
                            {{ formatDate(user.last_login_at) }}
                        </TableCell>
                        <TableCell>
                            <div class="flex gap-1">
                                <Button
                                    v-if="can.manage"
                                    variant="ghost"
                                    size="sm"
                                    @click="startEdit(user)"
                                >
                                    Edit
                                </Button>
                                <Button
                                    v-if="can.manage"
                                    variant="ghost"
                                    size="sm"
                                    @click="toggleStatus(user)"
                                >
                                    {{
                                        user.status === 'active'
                                            ? 'Disable'
                                            : 'Enable'
                                    }}
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>

                    <!-- Inline editor row -->
                    <TableRow v-if="editing === user.id">
                        <TableCell colspan="6" class="bg-muted/40">
                            <div
                                class="flex flex-col gap-4 py-2 md:flex-row md:items-end"
                            >
                                <div class="flex flex-col gap-1">
                                    <Label>Name</Label>
                                    <Input v-model="editName" class="w-56" />
                                </div>
                                <div class="flex flex-col gap-1">
                                    <Label>Status</Label>
                                    <select
                                        v-model="editStatus"
                                        class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                                    >
                                        <option value="active">Active</option>
                                        <option value="inactive">
                                            Inactive
                                        </option>
                                    </select>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <Label>Roles</Label>
                                    <div class="flex flex-wrap gap-2">
                                        <label
                                            v-for="role in roles"
                                            :key="role.id"
                                            class="flex items-center gap-1.5 text-sm"
                                        >
                                            <input
                                                type="checkbox"
                                                :checked="
                                                    editRoles.includes(role.id)
                                                "
                                                @change="toggleRole(role.id)"
                                            />
                                            {{ role.label }}
                                        </label>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <Button size="sm" @click="save"
                                        >Save</Button
                                    >
                                    <Button
                                        size="sm"
                                        variant="ghost"
                                        @click="editing = null"
                                        >Cancel</Button
                                    >
                                </div>
                            </div>
                        </TableCell>
                    </TableRow>
                </template>
                <TableRow v-if="!users.data.length">
                    <TableCell
                        colspan="6"
                        class="text-center text-muted-foreground"
                    >
                        No users found.
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
