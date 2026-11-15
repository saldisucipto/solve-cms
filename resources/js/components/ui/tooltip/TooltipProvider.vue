<script setup>

import { TooltipProvider } from "reka-ui"

const props = defineProps({ as: {}, asChild: {} })
</script>

<template>
  <TooltipProvider v-bind="{ ...props, ...$attrs }">
    <slot />
  </TooltipProvider>
</template>
