<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
    alt?: string;
}

const props = withDefaults(defineProps<Props>(), {
    alt: 'Africa Travel Bureau',
});
</script>

<template>
    <img
        src="/africa-travel-bureau.png"
        :alt="alt"
        :class="[className, 'object-contain']"
        v-bind="$attrs"
    />
</template>
