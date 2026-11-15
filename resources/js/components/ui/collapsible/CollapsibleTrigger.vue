<script setup>

import { CollapsibleTrigger } from "reka-ui"

const props = defineProps({ as: {}, asChild: {} })
</script>

<template>
  <CollapsibleTrigger
    data-slot="collapsible-trigger"
    v-bind="{ ...props, ...$attrs }"
  >
    <slot />
  </CollapsibleTrigger>
</template>
