<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Bell, CheckCheck } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    index as notificationsIndex,
    read as readRoute,
    readAll,
} from '@/routes/admin/notifications';

const page = usePage();
const feed = computed(() => page.props.notificationFeed ?? null);
const items = ref([]);
const unread = ref(0);

let channel = null;

function sync() {
    items.value = feed.value?.items ?? [];
    unread.value = feed.value?.unread ?? 0;
}

function markAllRead() {
    router.post(readAll(), {}, { preserveScroll: true });
}

function open(notification) {
    router.post(readRoute(notification.id), {}, { preserveScroll: true });
}

function subscribe() {
    const user = page.props.auth?.user;

    if (!user || !window.Echo || typeof window.Echo.private !== 'function') {
        return;
    }

    channel = window.Echo.private(`App.Models.User.${user.id}`);
    channel.notification(() => {
        router.reload({ only: ['notificationFeed'], preserveScroll: true });
    });
}

onMounted(() => {
    sync();
    subscribe();
});

onBeforeUnmount(() => {
    const user = page.props.auth?.user;

    if (channel && window.Echo && user) {
        window.Echo.leave(`App.Models.User.${user.id}`);
    }
});
</script>

<template>
    <DropdownMenu v-if="feed">
        <DropdownMenuTrigger as-child>
            <Button
                variant="ghost"
                size="icon"
                class="relative"
                aria-label="Notifications"
            >
                <Bell class="size-5" />
                <span
                    v-if="unread > 0"
                    class="absolute -top-0.5 -right-0.5 flex min-w-4 items-center justify-center rounded-full bg-destructive px-1 text-[10px] leading-4 text-white"
                >
                    {{ unread > 9 ? '9+' : unread }}
                </span>
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent align="end" class="w-80">
            <div class="flex items-center justify-between px-2 py-1.5">
                <DropdownMenuLabel class="p-0">Notifications</DropdownMenuLabel>
                <Button
                    v-if="unread > 0"
                    variant="ghost"
                    size="sm"
                    class="h-6 gap-1 text-xs"
                    @click="markAllRead"
                >
                    <CheckCheck class="size-3" />
                    Mark all read
                </Button>
            </div>
            <DropdownMenuSeparator />

            <div class="max-h-80 overflow-y-auto">
                <DropdownMenuItem
                    v-for="notification in items"
                    :key="notification.id"
                    class="flex cursor-pointer flex-col items-start gap-0.5 py-2"
                    :class="notification.read ? 'opacity-60' : ''"
                    @select="open(notification)"
                >
                    <span class="flex w-full items-center gap-2">
                        <span
                            v-if="!notification.read"
                            class="inline-block size-1.5 shrink-0 rounded-full bg-primary"
                        />
                        <span class="text-sm font-medium">{{
                            notification.title
                        }}</span>
                    </span>
                    <span class="pl-3.5 text-xs text-muted-foreground">{{
                        notification.body
                    }}</span>
                    <span class="pl-3.5 text-[10px] text-muted-foreground">{{
                        notification.created_at
                    }}</span>
                </DropdownMenuItem>

                <p
                    v-if="!items.length"
                    class="px-4 py-6 text-center text-xs text-muted-foreground"
                >
                    You're all caught up.
                </p>
            </div>

            <DropdownMenuSeparator />
            <DropdownMenuItem as-child class="justify-center">
                <Link
                    :href="notificationsIndex()"
                    class="w-full text-center text-xs"
                >
                    View all notifications
                </Link>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
