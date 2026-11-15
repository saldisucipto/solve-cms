<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({ class: {} });
</script>

<template>
    <th
        data-slot="table-head"
        :class="
            cn(
                'h-10 px-3 text-left align-middle font-medium whitespace-nowrap text-muted-foreground',
                props.class,
            )
        "
    >
        <slot />
    </th>
</template>
