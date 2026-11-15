<script setup>

import { SelectGroup } from "reka-ui"

const props = defineProps({ as: {}, asChild: {} })
</script>

<template>
  <SelectGroup
    data-slot="select-group"
    v-bind="{ ...props, ...$attrs }"
  >
    <slot />
  </SelectGroup>
</template>
