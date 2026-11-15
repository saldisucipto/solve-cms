<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    class: {},
});
</script>

<template>
    <div
        data-slot="table-container"
        class="relative w-full overflow-x-auto rounded-lg border"
    >
        <table
            data-slot="table"
            :class="cn('w-full caption-bottom text-sm', props.class)"
        >
            <slot />
        </table>
    </div>
</template>
