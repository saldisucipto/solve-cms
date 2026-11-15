<script setup>

import { SelectValue } from "reka-ui"

const props = defineProps({ as: {}, asChild: {} })
</script>

<template>
  <SelectValue
    data-slot="select-value"
    v-bind="{ ...props, ...$attrs }"
  >
    <slot />
  </SelectValue>
</template>
