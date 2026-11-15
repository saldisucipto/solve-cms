<script setup>

import { DropdownMenuRoot, useForwardPropsEmits } from "reka-ui"

const props = defineProps({ as: {}, asChild: {}, modelValue: {}, defaultValue: {} })
const emits = defineEmits(["update:modelValue"])

const forwarded = useForwardPropsEmits(props, emits)
</script>

<template>
  <DropdownMenuRoot
    v-slot="slotProps"
    data-slot="dropdown-menu"
    v-bind="{ ...forwarded, ...$attrs }"
  >
    <slot v-bind="slotProps" />
  </DropdownMenuRoot>
</template>
