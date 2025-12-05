<script setup lang="ts">
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Switch } from '@/components/ui/switch';
import { useToast } from '@/components/ui/toast';

interface Props {
    agentId: number;
    currentStatus: string;
    localStatus?: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:localStatus': [status: string];
}>();

const { success, error } = useToast();

const isActive = computed(() => {
    return (props.localStatus || props.currentStatus) === 'active';
});

const handleChange = (checked: boolean) => {
    const newStatus = checked ? 'active' : 'inactive';
    emit('update:localStatus', newStatus);
    
    const previousStatus = props.localStatus || props.currentStatus;
    const action = checked ? 'activate' : 'suspend';
    const actionText = checked ? 'activated' : 'suspended';
    
    router.post(`/admin/agent/${action}/${props.agentId}`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            success(
                `Account ${actionText}`,
                `Agent account has been ${actionText} successfully.`
            );
        },
        onError: () => {
            // Rollback on error
            emit('update:localStatus', previousStatus);
            error(
                'Action failed',
                `Failed to ${action} agent account. Please try again.`
            );
        },
    });
};
</script>

<template>
    <Switch
        :checked="isActive"
        @checked-change="handleChange"
    />
</template>

