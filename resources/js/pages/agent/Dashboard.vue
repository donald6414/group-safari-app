<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { agentDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { Stat } from '@/types/stat';
import StartCard from '@/components/custom/StartCard.vue';

const props = defineProps<{
    responseData: {
        stats: Stat[];
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: agentDashboard().url,
    },
];
</script>

<template>
    <Head title="Agent Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="grid gap-4 md:grid-cols-4">
                <StartCard
                    v-for="(item, index) in props.responseData.stats"
                    :key="index"
                    :title="item.title"
                    :value="item.value"
                />
            </div>
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
            >
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>
