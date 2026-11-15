<script setup>

import { SelectItemText } from "reka-ui"

const props = defineProps({ as: {}, asChild: {} })
</script>

<template>
  <SelectItemText
    data-slot="select-item-text"
    v-bind="{ ...props, ...$attrs }"
  >
    <slot />
  </SelectItemText>
</template>
