<script setup>

import {
  DropdownMenuRadioGroup,
  useForwardPropsEmits,
} from "reka-ui"

const props = defineProps({ as: {}, asChild: {} })
const emits = defineEmits()

const forwarded = useForwardPropsEmits(props, emits)
</script>

<template>
  <DropdownMenuRadioGroup
    data-slot="dropdown-menu-radio-group"
    v-bind="{ ...forwarded, ...$attrs }"
  >
    <slot />
  </DropdownMenuRadioGroup>
</template>
