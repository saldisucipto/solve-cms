<script setup>

import { DialogTrigger } from "reka-ui"

const props = defineProps({ as: {}, asChild: {} })
</script>

<template>
  <DialogTrigger
    data-slot="dialog-trigger"
    v-bind="{ ...props, ...$attrs }"
  >
    <slot />
  </DialogTrigger>
</template>
