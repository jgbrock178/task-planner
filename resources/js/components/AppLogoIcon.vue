<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { BadgeCheck } from 'lucide-vue-next'

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <BadgeCheck class="size-5"></BadgeCheck>
</template>
