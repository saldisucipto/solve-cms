<script setup>
defineProps({
    links: { type: Array, default: () => [] },
});
</script>

<template>
    <nav
        v-if="links.length > 3"
        class="mt-10 flex flex-wrap items-center justify-center gap-1"
        aria-label="Pagination"
    >
        <template v-for="(link, index) in links" :key="index">
            <a
                v-if="link.url"
                :href="link.url"
                class="rounded-md border px-3 py-1.5 text-sm"
                :class="
                    link.active
                        ? 'border-[var(--fe-primary)] bg-[var(--fe-primary)] text-white'
                        : 'border-black/15 hover:border-[var(--fe-primary)]'
                "
                v-html="link.label"
            />
            <span
                v-else
                class="rounded-md border border-black/10 px-3 py-1.5 text-sm text-black/30"
                v-html="link.label"
            />
        </template>
    </nav>
</template>
