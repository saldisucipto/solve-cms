<script setup>

import { AvatarImage } from "reka-ui"

const props = defineProps({ as: {}, asChild: {} })
</script>

<template>
  <AvatarImage
    data-slot="avatar-image"
    v-bind="{ ...props, ...$attrs }"
    class="aspect-square size-full"
  >
    <slot />
  </AvatarImage>
</template>
