<script setup>

import { TooltipTrigger } from "reka-ui"

const props = defineProps({ as: {}, asChild: {} })
</script>

<template>
  <TooltipTrigger
    data-slot="tooltip-trigger"
    v-bind="{ ...props, ...$attrs }"
  >
    <slot />
  </TooltipTrigger>
</template>
