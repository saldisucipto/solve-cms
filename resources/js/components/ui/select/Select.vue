<script setup>

import { SelectRoot, useForwardPropsEmits } from "reka-ui"

const props = defineProps({ as: {}, asChild: {}, modelValue: {}, defaultValue: {} })
const emits = defineEmits(["update:modelValue"])

const forwarded = useForwardPropsEmits(props, emits)
</script>

<template>
  <SelectRoot
    v-slot="slotProps"
    data-slot="select"
    v-bind="{ ...forwarded, ...$attrs }"
  >
    <slot v-bind="slotProps" />
  </SelectRoot>
</template>
