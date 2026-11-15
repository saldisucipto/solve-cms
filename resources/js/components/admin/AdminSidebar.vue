<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronDown, LogOut, Settings, UserRound } from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import { navigationGroups, canAccess } from '@/components/admin/navigation';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { dashboard } from '@/routes/admin';
import { edit as profileEdit } from '@/routes/profile';
import { edit as securityEdit } from '@/routes/security';
import { logout } from '@/routes';

defineEmits(['navigate']);

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const { isCurrentOrParentUrl } = useCurrentUrl();
const userMenuOpen = ref(false);

const groups = computed(() =>
    navigationGroups
        .map((group) => ({
            ...group,
            items: group.items.filter((item) =>
                canAccess(user.value, item.permission),
            ),
        }))
        .filter((group) => group.items.length > 0),
);
</script>

<template>
    <div class="flex h-full flex-col">
        <div class="flex h-16 shrink-0 items-center border-b px-4">
            <Link
                :href="dashboard()"
                class="flex items-center gap-2"
                @click="$emit('navigate')"
            >
                <AppLogo />
            </Link>
        </div>

        <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-4">
            <div v-for="group in groups" :key="group.label">
                <p
                    class="mb-1.5 px-2 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                >
                    {{ group.label }}
                </p>

                <ul class="space-y-0.5">
                    <li v-for="item in group.items" :key="item.title">
                        <span
                            v-if="item.upcoming"
                            class="flex cursor-not-allowed items-center gap-2 rounded-md px-2 py-1.5 text-sm text-muted-foreground/60"
                            :title="`${item.title} arrives in a later phase`"
                        >
                            <component
                                :is="item.icon"
                                class="size-4 shrink-0"
                            />
                            {{ item.title }}
                            <span
                                class="ml-auto rounded border px-1 text-[10px]"
                                >Soon</span
                            >
                        </span>

                        <Link
                            v-else
                            :href="item.href"
                            class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm transition-colors"
                            :class="
                                isCurrentOrParentUrl(item.href)
                                    ? 'bg-primary/10 font-medium text-primary'
                                    : 'text-foreground/80 hover:bg-muted'
                            "
                            @click="$emit('navigate')"
                        >
                            <component
                                :is="item.icon"
                                class="size-4 shrink-0"
                            />
                            {{ item.title }}
                        </Link>
                    </li>
                </ul>
            </div>
        </nav>

        <div v-if="user" class="border-t p-3">
            <div class="relative">
                <button
                    type="button"
                    class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-left text-sm hover:bg-muted"
                    @click="userMenuOpen = !userMenuOpen"
                >
                    <span
                        class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-semibold text-primary"
                    >
                        {{ user.name?.charAt(0)?.toUpperCase() }}
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate font-medium">{{
                            user.name
                        }}</span>
                        <span
                            class="block truncate text-xs text-muted-foreground"
                        >
                            {{ user.roles?.[0] ?? user.email }}
                        </span>
                    </span>
                    <ChevronDown
                        class="size-4 shrink-0 text-muted-foreground"
                    />
                </button>

                <div
                    v-if="userMenuOpen"
                    class="absolute bottom-full left-0 mb-1 w-full rounded-md border bg-popover p-1 shadow-md"
                >
                    <Link
                        :href="profileEdit()"
                        class="flex items-center gap-2 rounded px-2 py-1.5 text-sm hover:bg-muted"
                        @click="
                            userMenuOpen = false;
                            $emit('navigate');
                        "
                    >
                        <UserRound class="size-4" />
                        Profile
                    </Link>
                    <Link
                        :href="securityEdit()"
                        class="flex items-center gap-2 rounded px-2 py-1.5 text-sm hover:bg-muted"
                        @click="
                            userMenuOpen = false;
                            $emit('navigate');
                        "
                    >
                        <Settings class="size-4" />
                        Security
                    </Link>
                    <Link
                        :href="logout()"
                        method="post"
                        as="button"
                        class="flex w-full items-center gap-2 rounded px-2 py-1.5 text-left text-sm text-destructive hover:bg-muted"
                    >
                        <LogOut class="size-4" />
                        Log out
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
