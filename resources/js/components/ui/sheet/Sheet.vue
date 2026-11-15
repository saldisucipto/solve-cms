<script setup>

import { DialogRoot, useForwardPropsEmits } from "reka-ui"

const props = defineProps({ as: {}, asChild: {}, modelValue: {}, defaultValue: {} })
const emits = defineEmits(["update:modelValue"])

const forwarded = useForwardPropsEmits(props, emits)
</script>

<template>
  <DialogRoot
    v-slot="slotProps"
    data-slot="sheet"
    v-bind="{ ...forwarded, ...$attrs }"
  >
    <slot v-bind="slotProps" />
  </DialogRoot>
</template>
