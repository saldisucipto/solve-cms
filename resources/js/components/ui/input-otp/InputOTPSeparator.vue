<script setup>

import { MinusIcon } from "@lucide/vue"
import { useForwardProps } from "reka-ui"

const props = defineProps({ class: {} })

const forwarded = useForwardProps(props)
</script>

<template>
  <div
    data-slot="input-otp-separator"
    role="separator"
    v-bind="forwarded"
  >
    <slot>
      <MinusIcon />
    </slot>
  </div>
</template>
