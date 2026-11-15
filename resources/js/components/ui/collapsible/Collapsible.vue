<script setup>

import { CollapsibleRoot, useForwardPropsEmits } from "reka-ui"

const props = defineProps({ as: {}, asChild: {}, modelValue: {}, defaultValue: {} })
const emits = defineEmits(["update:modelValue"])

const forwarded = useForwardPropsEmits(props, emits)
</script>

<template>
  <CollapsibleRoot
    v-slot="slotProps"
    data-slot="collapsible"
    v-bind="{ ...forwarded, ...$attrs }"
  >
    <slot v-bind="slotProps" />
  </CollapsibleRoot>
</template>
