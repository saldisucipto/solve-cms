<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({ class: {} });
</script>

<template>
    <tr
        data-slot="table-row"
        :class="
            cn(
                'border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted',
                props.class,
            )
        "
    >
        <slot />
    </tr>
</template>
