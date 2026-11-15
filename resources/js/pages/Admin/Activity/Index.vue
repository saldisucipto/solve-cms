<script setup>
import { Head, router } from '@inertiajs/vue3';
import { Activity } from '@lucide/vue';
import { ref } from 'vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { dashboard } from '@/routes/admin';
import { index } from '@/routes/admin/activity';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Activity Logs', href: index() },
        ],
    },
});

const props = defineProps({
    logs: { type: Object, required: true },
    events: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');
const event = ref(props.filters.event ?? '');
const user = ref(props.filters.user ? String(props.filters.user) : '');

function applyFilters() {
    router.get(
        index(),
        {
            search: search.value,
            event: event.value,
            user: user.value === '' ? '' : user.value,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function resetFilters() {
    search.value = '';
    event.value = '';
    user.value = '';
    applyFilters();
}

function badgeVariant(name) {
    if (name.endsWith('deleted') || name.endsWith('removed')) {
        return 'destructive';
    }

    if (
        name.endsWith('created') ||
        name.endsWith('uploaded') ||
        name.endsWith('published')
    ) {
        return 'default';
    }

    return 'secondary';
}
</script>

<template>
    <Head title="Activity Logs" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader
            title="Activity Logs"
            description="Every administrative action recorded by the application."
        />

        <Card>
            <CardContent class="flex flex-wrap items-end gap-3 pt-4">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-muted-foreground"
                        >Search</label
                    >
                    <Input
                        v-model="search"
                        placeholder="Search descriptions…"
                        class="w-56"
                        @keydown.enter="applyFilters"
                    />
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-muted-foreground"
                        >Event</label
                    >
                    <select
                        v-model="event"
                        class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                    >
                        <option value="">All events</option>
                        <option
                            v-for="name in events"
                            :key="name"
                            :value="name"
                        >
                            {{ name }}
                        </option>
                    </select>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-muted-foreground"
                        >User</label
                    >
                    <select
                        v-model="user"
                        class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                    >
                        <option value="">All users</option>
                        <option
                            v-for="person in users"
                            :key="person.id"
                            :value="String(person.id)"
                        >
                            {{ person.name }}
                        </option>
                    </select>
                </div>

                <Button variant="secondary" @click="applyFilters"
                    >Filter</Button
                >
                <Button variant="ghost" @click="resetFilters">Reset</Button>
            </CardContent>
        </Card>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead class="w-40">Event</TableHead>
                    <TableHead>Description</TableHead>
                    <TableHead class="hidden md:table-cell">User</TableHead>
                    <TableHead class="hidden lg:table-cell">Subject</TableHead>
                    <TableHead class="hidden xl:table-cell">IP</TableHead>
                    <TableHead class="hidden sm:table-cell">When</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="log in logs.data" :key="log.id">
                    <TableCell>
                        <Badge
                            :variant="badgeVariant(log.event)"
                            class="text-[10px]"
                        >
                            {{ log.event }}
                        </Badge>
                    </TableCell>
                    <TableCell class="text-sm">{{ log.description }}</TableCell>
                    <TableCell class="hidden text-sm md:table-cell">
                        {{ log.user ?? 'System' }}
                    </TableCell>
                    <TableCell
                        class="hidden text-xs text-muted-foreground lg:table-cell"
                    >
                        {{
                            log.subject_type
                                ? `${log.subject_type} #${log.subject_id}`
                                : '—'
                        }}
                    </TableCell>
                    <TableCell
                        class="hidden text-xs text-muted-foreground xl:table-cell"
                    >
                        {{ log.ip_address ?? '—' }}
                    </TableCell>
                    <TableCell
                        class="hidden text-xs text-muted-foreground sm:table-cell"
                    >
                        {{ log.created_human }}
                    </TableCell>
                </TableRow>

                <TableRow v-if="!logs.data.length">
                    <TableCell
                        colspan="6"
                        class="py-10 text-center text-muted-foreground"
                    >
                        <Activity class="mx-auto mb-2 size-5" />
                        No activity recorded yet.
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <div
            v-if="logs.last_page > 1"
            class="flex items-center justify-between text-sm text-muted-foreground"
        >
            <span
                >Showing {{ logs.from }}–{{ logs.to }} of {{ logs.total }}</span
            >
            <div class="flex gap-1">
                <Button
                    v-for="link in logs.links"
                    :key="link.label"
                    :variant="link.active ? 'default' : 'ghost'"
                    size="sm"
                    :disabled="!link.url"
                    as-child
                >
                    <a v-if="link.url" :href="link.url" v-html="link.label" />
                    <span v-else v-html="link.label" />
                </Button>
            </div>
        </div>
    </div>
</template>
