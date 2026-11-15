<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

const page = usePage();
const branding = computed(
    () => page.props.branding ?? { name: page.props.name, logo: null },
);
const name = computed(() => branding.value.name ?? page.props.name);
const logo = computed(() => branding.value.logo ?? null);
</script>

<template>
    <div class="flex min-w-0 items-center gap-2">
        <img
            v-if="logo"
            :src="logo"
            :alt="name"
            class="h-8 w-auto max-w-28 object-contain"
        />
        <div
            v-else
            class="flex aspect-square size-8 shrink-0 items-center justify-center rounded-md bg-primary text-primary-foreground"
        >
            <AppLogoIcon class="size-5" />
        </div>

        <span class="truncate text-sm font-semibold">{{ name }}</span>
    </div>
</template>
