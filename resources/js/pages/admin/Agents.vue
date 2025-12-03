<script setup lang="ts">
import { ref, unref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { adminAgentsList } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import StartCard from '@/components/custom/StartCard.vue';
import { type Stat } from '@/types/stat';
import { type User } from '@/types';
import DataTable from '@/components/custom/table/DataTable.vue';
import { type Column } from '@/types';
import { Button } from '@/components/ui/button';
import { UserPlus } from 'lucide-vue-next';
import CreateAccount from '@/components/custom/modal/CreateAccount.vue';

const props = defineProps<{
    responseData: {
        stats: Stat[];
        agents: User[];
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Agents List',
        href: adminAgentsList().url,
    },
];

// Modal state
const isModalOpen = ref(false);

const openModal = () => {
    isModalOpen.value = true;
};

const handleModalUpdate = (value: boolean) => {
    isModalOpen.value = value;
};

const columns: Column[] = [
    {
        key: 'name',
        label: 'Name',
    },
    {
        key: 'email',
        label: 'Email',
    },
    {
        key: 'phone',
        label: 'Phone',
    },
    {
        key: 'status',
        label: 'Status',
    },
    {
        key: 'created_at',
        label: 'Created At',
    },
];
</script>

<template>
    <Head title="Agents List" />

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
            
            <div class="w-full flex justify-end">
                <Button @click="openModal">
                    <UserPlus class="mr-2 h-4 w-4" />
                    Create Agent Account
                </Button>
            </div>
            
            <div>
                <DataTable :columns="columns" :data="props.responseData.agents" />
            </div>

            <CreateAccount
                :open="unref(isModalOpen)"
                @update:open="handleModalUpdate"
            />
        </div>
    </AppLayout>
</template>
