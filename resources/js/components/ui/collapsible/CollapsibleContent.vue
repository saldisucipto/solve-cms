<script setup>

import { CollapsibleContent } from "reka-ui"

const props = defineProps({ as: {}, asChild: {} })
</script>

<template>
  <CollapsibleContent
    data-slot="collapsible-content"
    v-bind="{ ...props, ...$attrs }"
  >
    <slot />
  </CollapsibleContent>
</template>
