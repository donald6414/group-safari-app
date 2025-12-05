<script setup lang="ts">
import { ref, unref, h, reactive, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { adminAgentsList } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import StartCard from '@/components/custom/StartCard.vue';
import { type Stat } from '@/types/stat';
import { type User } from '@/types';
import DataTable from '@/components/custom/table/DataTable.vue';
import { type Column } from '@/types';
import { Button } from '@/components/ui/button';
import { UserPlus } from 'lucide-vue-next';
import CreateAccount from '@/components/custom/modal/CreateAccount.vue';
import { Toaster, useToast } from '@/components/ui/toast';
import { Switch } from '@/components/ui/switch';

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

// Toast notifications
const { success, error } = useToast();

// Local state for optimistic updates
const localAgents = reactive<Record<number, { status: string }>>({});

// Initialize local state with current agents
props.responseData.agents.forEach(agent => {
    localAgents[agent.id] = { status: agent.status };
});

// Computed agents that merge local state with original data
const agentsWithLocalState = computed(() => {
    return props.responseData.agents.map(agent => ({
        ...agent,
        status: localAgents[agent.id]?.status || agent.status,
    }));
});

// Create a key that changes when localAgents changes to force table re-render
const tableKey = computed(() => {
    return Object.values(localAgents)
        .map(a => a.status)
        .join('-');
});

// Modal state
const isModalOpen = ref(false);

const openModal = () => {
    isModalOpen.value = true;
};

const handleModalUpdate = (value: boolean) => {
    isModalOpen.value = value;
};

// Make columns computed so render function reacts to localAgents changes
const columns = computed<Column[]>(() => [
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
        sortable: false,
        align: 'center',
        width: '120px',
        render: (value, row: User) => {
            if (!row || !row.id) return '';
            
            // Access localAgents reactively - this ensures re-render when localAgents changes
            const currentStatus = localAgents[row.id]?.status || value;
            const isActive = currentStatus === 'active';
            
            return h(Switch, {
                key: `switch-${row.id}-${currentStatus}`, // Key forces re-render when status changes
                checked: isActive,
                onCheckedChange: (checked: boolean) => {
                    // Optimistic update - update local state immediately
                    const previousStatus = localAgents[row.id]?.status || value;
                    localAgents[row.id] = { status: checked ? 'active' : 'inactive' };
                    
                    const action = checked ? 'activate' : 'suspend';
                    const actionText = checked ? 'activated' : 'suspended';
                    
                    router.post(`/admin/agent/${action}/${row.id}`, {}, {
                        preserveScroll: true,
                        onSuccess: () => {
                            success(
                                `Account ${actionText}`,
                                `Agent account has been ${actionText} successfully.`
                            );
                        },
                        onError: () => {
                            // Rollback on error
                            localAgents[row.id] = { status: previousStatus };
                            error(
                                'Action failed',
                                `Failed to ${action} agent account. Please try again.`
                            );
                        },
                    });
                },
            });
        },
    },
    {
        key: 'created_at',
        label: 'Created At',
    },
]);
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
                <DataTable 
                    :key="`agents-table-${tableKey}`"
                    :columns="columns as any" 
                    :data="agentsWithLocalState as any" 
                />
            </div>

            <CreateAccount
                :open="unref(isModalOpen)"
                @update:open="handleModalUpdate"
            />
            
            <!-- Toast Notifications -->
            <Toaster />
        </div>
    </AppLayout>
</template>
