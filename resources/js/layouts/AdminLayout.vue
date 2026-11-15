<script setup>
import { ref } from 'vue';
import AdminSidebar from '@/components/admin/AdminSidebar.vue';
import AdminTopbar from '@/components/admin/AdminTopbar.vue';
import { Toaster } from '@/components/ui/sonner';

defineProps({
    breadcrumbs: { type: Array, default: () => [] },
});

// The drawer is only used below the lg breakpoint; the sidebar is always
// visible from lg upwards. Keeping this in plain CSS avoids any layout state
// that could get out of sync between renders.
const drawerOpen = ref(false);
</script>

<template>
    <div class="min-h-screen bg-background text-foreground">
        <aside
            class="fixed inset-y-0 left-0 z-40 hidden w-64 border-r bg-card lg:block"
            aria-label="Main navigation"
        >
            <AdminSidebar />
        </aside>

        <div v-if="drawerOpen" class="fixed inset-0 z-50 lg:hidden">
            <div
                class="absolute inset-0 bg-black/50"
                @click="drawerOpen = false"
            />
            <aside
                class="absolute inset-y-0 left-0 w-72 bg-card shadow-xl"
                aria-label="Main navigation"
            >
                <AdminSidebar @navigate="drawerOpen = false" />
            </aside>
        </div>

        <div class="flex min-h-screen flex-col lg:pl-64">
            <AdminTopbar
                :breadcrumbs="breadcrumbs"
                @toggle-sidebar="drawerOpen = !drawerOpen"
            />

            <main class="min-w-0 flex-1">
                <slot />
            </main>
        </div>

        <Toaster />
    </div>
</template>
