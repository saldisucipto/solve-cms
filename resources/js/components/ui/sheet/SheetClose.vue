<script setup>

import { DialogClose } from "reka-ui"

const props = defineProps({ as: {}, asChild: {} })
</script>

<template>
  <DialogClose
    data-slot="sheet-close"
    v-bind="{ ...props, ...$attrs }"
  >
    <slot />
  </DialogClose>
</template>
