<script setup>

import { DropdownMenuGroup } from "reka-ui"

const props = defineProps({ as: {}, asChild: {} })
</script>

<template>
  <DropdownMenuGroup
    data-slot="dropdown-menu-group"
    v-bind="{ ...props, ...$attrs }"
  >
    <slot />
  </DropdownMenuGroup>
</template>
