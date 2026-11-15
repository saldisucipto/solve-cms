<script setup>

import { TooltipRoot, useForwardPropsEmits } from "reka-ui"

const props = defineProps({ as: {}, asChild: {}, modelValue: {}, defaultValue: {} })
const emits = defineEmits(["update:modelValue"])

const forwarded = useForwardPropsEmits(props, emits)
</script>

<template>
  <TooltipRoot
    v-slot="slotProps"
    data-slot="tooltip"
    v-bind="{ ...forwarded, ...$attrs }"
  >
    <slot v-bind="slotProps" />
  </TooltipRoot>
</template>
