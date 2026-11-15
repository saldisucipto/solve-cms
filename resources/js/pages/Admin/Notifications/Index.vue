<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { Bell, CheckCheck } from '@lucide/vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { dashboard } from '@/routes/admin';
import { index, read, readAll } from '@/routes/admin/notifications';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Notifications', href: index() },
        ],
    },
});

defineProps({
    notifications: { type: Object, required: true },
    unread: { type: Number, default: 0 },
});

function open(notification) {
    router.post(read(notification.id), {}, { preserveScroll: true });
}

function markAllRead() {
    router.post(readAll(), {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Notifications" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader title="Notifications" :description="`${unread} unread`">
            <template #actions>
                <Button
                    v-if="unread > 0"
                    variant="outline"
                    @click="markAllRead"
                >
                    <CheckCheck class="size-4" />
                    Mark all read
                </Button>
            </template>
        </PageHeader>

        <Card>
            <CardContent class="divide-y p-0">
                <button
                    v-for="notification in notifications.data"
                    :key="notification.id"
                    type="button"
                    class="flex w-full items-start gap-3 px-4 py-3 text-left hover:bg-muted"
                    :class="notification.read ? 'opacity-60' : ''"
                    @click="open(notification)"
                >
                    <Bell
                        class="mt-0.5 size-4 shrink-0 text-muted-foreground"
                    />
                    <span class="flex flex-1 flex-col gap-0.5">
                        <span
                            class="flex items-center gap-2 text-sm font-medium"
                        >
                            {{ notification.title }}
                            <Badge
                                v-if="!notification.read"
                                variant="default"
                                class="text-[10px]"
                            >
                                New
                            </Badge>
                        </span>
                        <span class="text-xs text-muted-foreground">{{
                            notification.body
                        }}</span>
                        <span class="text-[10px] text-muted-foreground">{{
                            notification.created_at
                        }}</span>
                    </span>
                </button>

                <p
                    v-if="!notifications.data.length"
                    class="px-4 py-12 text-center text-sm text-muted-foreground"
                >
                    No notifications yet.
                </p>
            </CardContent>
        </Card>

        <div
            v-if="notifications.last_page > 1"
            class="flex justify-center gap-1"
        >
            <Button
                v-for="link in notifications.links"
                :key="link.label"
                :variant="link.active ? 'default' : 'ghost'"
                size="sm"
                :disabled="!link.url"
                as-child
            >
                <Link v-if="link.url" :href="link.url" v-html="link.label" />
                <span v-else v-html="link.label" />
            </Button>
        </div>
    </div>
</template>
