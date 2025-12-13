<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useToast } from '@/components/ui/toast';
import { AlertTriangle, Trash2 } from 'lucide-vue-next';

const props = defineProps<{
    open: boolean;
    vehicleId: number;
    vehicleName?: string;
    tourTitle?: string;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

// Toast notifications
const { success, error } = useToast();

// Processing state
const processing = ref<boolean>(false);

// Handle close
const handleClose = () => {
    if (!processing.value) {
        emit('update:open', false);
    }
};

// Handle confirm - delete vehicle
const handleConfirm = () => {
    processing.value = true;

    router.delete(
        `/admin/delete/vehicle/${props.vehicleId}`,
        {
            preserveScroll: true,
            onSuccess: () => {
                success(
                    'Vehicle Deleted',
                    'The vehicle has been successfully removed from this tour.'
                );
                // Close modal after a short delay to show the toast
                setTimeout(() => {
                    handleClose();
                }, 500);
            },
            onError: (pageErrors: any) => {
                // Handle different error formats from Laravel/Inertia
                let errorMessage = 'Failed to delete vehicle. Please try again.';
                
                if (pageErrors && typeof pageErrors === 'object') {
                    // Check for validation errors in errors object
                    if (pageErrors.errors && typeof pageErrors.errors === 'object') {
                        const errors = pageErrors.errors;
                        if (errors.vehicleId) {
                            errorMessage = Array.isArray(errors.vehicleId) ? errors.vehicleId[0] : String(errors.vehicleId);
                        } else if (errors.message) {
                            errorMessage = Array.isArray(errors.message) ? errors.message[0] : String(errors.message);
                        }
                    }
                    // Check for direct field error
                    else if (pageErrors.vehicleId) {
                        errorMessage = Array.isArray(pageErrors.vehicleId) ? pageErrors.vehicleId[0] : String(pageErrors.vehicleId);
                    }
                    // Check for general error message
                    else if (pageErrors.message) {
                        errorMessage = String(pageErrors.message);
                    }
                }
                
                error('Failed to Delete Vehicle', errorMessage);
            },
            onFinish: () => {
                processing.value = false;
            },
        }
    );
};
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100">
                        <Trash2 class="h-5 w-5 text-red-600" />
                    </div>
                    <div>
                        <DialogTitle class="text-xl">Delete Vehicle</DialogTitle>
                        <DialogDescription class="mt-1">
                            Are you sure you want to remove this vehicle from the tour?
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>

            <div class="py-4">
                <div class="rounded-lg border bg-muted p-4 space-y-2">
                    <p class="text-sm font-medium text-foreground">Vehicle Information:</p>
                    <p class="text-sm text-muted-foreground">
                        <strong>Tour:</strong> {{ tourTitle || 'N/A' }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        <strong>Vehicle:</strong> {{ vehicleName || `Vehicle #${vehicleId}` }}
                    </p>
                </div>
                
                <div class="mt-4 rounded-lg border bg-red-50 p-4">
                    <div class="flex items-start gap-3">
                        <AlertTriangle class="h-5 w-5 text-red-600 shrink-0 mt-0.5" />
                        <div>
                            <p class="text-sm font-medium text-red-900 mb-1">Warning: This action cannot be undone</p>
                            <p class="text-sm text-red-800">
                                All seats and associated data for this vehicle will be permanently deleted. Make sure this vehicle has no reserved or booked seats before proceeding.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <DialogFooter class="flex gap-3">
                <Button 
                    variant="outline" 
                    @click="handleClose" 
                    class="flex-1"
                    :disabled="processing"
                >
                    Cancel
                </Button>
                <Button 
                    variant="destructive"
                    @click="handleConfirm" 
                    class="flex-1"
                    :disabled="processing"
                >
                    <span v-if="processing">Deleting...</span>
                    <span v-else>Delete Vehicle</span>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

