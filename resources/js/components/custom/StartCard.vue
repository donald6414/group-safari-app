<script setup lang="ts">
defineProps<{
    title: string;
    value: string | number;
}>();
</script>

<template>
    <div
        class="flex flex-col gap-2 rounded-lg border border-border bg-card p-6"
    >
        <p class="text-sm font-medium text-muted-foreground">
            {{ title }}
        </p>
        <p class="text-2xl font-semibold text-card-foreground">
            {{ value }}
        </p>
    </div>
</template>

