<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Bell, CheckCheck, ExternalLink, Menu, Search } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { home } from '@/routes';
import {
    index as notificationsIndex,
    read as readRoute,
    readAll,
} from '@/routes/admin/notifications';

defineProps({
    breadcrumbs: { type: Array, default: () => [] },
});

defineEmits(['toggle-sidebar']);

const page = usePage();
const feed = computed(() => page.props.notificationFeed ?? null);
const items = computed(() => feed.value?.items ?? []);
const unread = computed(() => feed.value?.unread ?? 0);
const open = ref(false);

let channel = null;
const root = ref(null);

function toggle() {
    open.value = !open.value;
}

function markAllRead() {
    router.post(readAll(), {}, { preserveScroll: true });
    open.value = false;
}

function openNotification(notification) {
    open.value = false;
    router.post(readRoute(notification.id), {}, { preserveScroll: true });
}

function onClickOutside(event) {
    if (root.value && !root.value.contains(event.target)) {
        open.value = false;
    }
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
    document.addEventListener('click', onClickOutside);
    subscribe();
});

onBeforeUnmount(() => {
    document.removeEventListener('click', onClickOutside);

    const user = page.props.auth?.user;

    if (channel && window.Echo && user) {
        window.Echo.leave(`App.Models.User.${user.id}`);
    }
});
</script>

<template>
    <header
        class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b bg-background px-4"
    >
        <Button
            variant="ghost"
            size="icon"
            class="size-9 lg:hidden"
            aria-label="Toggle navigation"
            @click="$emit('toggle-sidebar')"
        >
            <Menu class="size-5" />
        </Button>

        <nav
            v-if="breadcrumbs.length"
            class="hidden items-center gap-2 text-sm md:flex"
            aria-label="Breadcrumb"
        >
            <template v-for="(crumb, index) in breadcrumbs" :key="crumb.title">
                <span v-if="index > 0" class="text-muted-foreground">/</span>
                <Link
                    v-if="crumb.href"
                    :href="crumb.href"
                    class="text-muted-foreground hover:text-foreground"
                >
                    {{ crumb.title }}
                </Link>
                <span v-else class="font-medium">{{ crumb.title }}</span>
            </template>
        </nav>

        <div class="ml-auto flex items-center gap-2">
            <div class="relative hidden sm:block">
                <Search
                    class="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    class="w-56 pl-8"
                    placeholder="Search content…"
                    aria-label="Search content"
                    disabled
                    title="Global search arrives with a later phase"
                />
            </div>

            <div v-if="feed" ref="root" class="relative">
                <Button
                    variant="ghost"
                    size="icon"
                    class="relative"
                    aria-label="Notifications"
                    @click="toggle"
                >
                    <Bell class="size-5" />
                    <span
                        v-if="unread > 0"
                        class="absolute -top-0.5 -right-0.5 flex min-w-4 items-center justify-center rounded-full bg-destructive px-1 text-[10px] leading-4 text-white"
                    >
                        {{ unread > 9 ? '9+' : unread }}
                    </span>
                </Button>

                <div
                    v-if="open"
                    class="absolute right-0 z-40 mt-2 w-80 overflow-hidden rounded-md border bg-popover shadow-lg"
                >
                    <div
                        class="flex items-center justify-between border-b px-3 py-2"
                    >
                        <span class="text-sm font-medium">Notifications</span>
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

                    <div class="max-h-80 overflow-y-auto">
                        <button
                            v-for="notification in items"
                            :key="notification.id"
                            type="button"
                            class="flex w-full flex-col items-start gap-0.5 border-b px-3 py-2 text-left last:border-b-0 hover:bg-muted"
                            :class="notification.read ? 'opacity-60' : ''"
                            @click="openNotification(notification)"
                        >
                            <span
                                class="flex items-center gap-2 text-sm font-medium"
                            >
                                <span
                                    v-if="!notification.read"
                                    class="inline-block size-1.5 rounded-full bg-primary"
                                />
                                {{ notification.title }}
                            </span>
                            <span class="text-xs text-muted-foreground">{{
                                notification.body
                            }}</span>
                            <span class="text-[10px] text-muted-foreground">{{
                                notification.created_at
                            }}</span>
                        </button>

                        <p
                            v-if="!items.length"
                            class="px-3 py-6 text-center text-xs text-muted-foreground"
                        >
                            You're all caught up.
                        </p>
                    </div>

                    <Link
                        :href="notificationsIndex()"
                        class="block border-t px-3 py-2 text-center text-xs hover:bg-muted"
                        @click="open = false"
                    >
                        View all notifications
                    </Link>
                </div>
            </div>

            <Button variant="outline" size="sm" as-child>
                <Link :href="home()" target="_blank" rel="noopener">
                    <ExternalLink class="size-4" />
                    <span class="hidden sm:inline">View site</span>
                </Link>
            </Button>
        </div>
    </header>
</template>
