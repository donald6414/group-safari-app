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
import { AlertTriangle } from 'lucide-vue-next';

const props = defineProps<{
    open: boolean;
    tourId: number;
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

// Handle confirm - add vehicle
const handleConfirm = () => {
    processing.value = true;

    router.post(
        `/admin/add/vehicle/${props.tourId}`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                success(
                    'Vehicle Added',
                    'A new vehicle with 6 seats has been successfully added to this tour.'
                );
                // Close modal after a short delay to show the toast
                setTimeout(() => {
                    handleClose();
                }, 500);
            },
            onError: (pageErrors: any) => {
                // Handle different error formats from Laravel/Inertia
                let errorMessage = 'Failed to add vehicle. Please try again.';
                
                if (pageErrors && typeof pageErrors === 'object') {
                    // Check for validation errors in errors object
                    if (pageErrors.errors && typeof pageErrors.errors === 'object') {
                        const errors = pageErrors.errors;
                        if (errors.tourId) {
                            errorMessage = Array.isArray(errors.tourId) ? errors.tourId[0] : String(errors.tourId);
                        } else if (errors.message) {
                            errorMessage = Array.isArray(errors.message) ? errors.message[0] : String(errors.message);
                        }
                    }
                    // Check for direct field error
                    else if (pageErrors.tourId) {
                        errorMessage = Array.isArray(pageErrors.tourId) ? pageErrors.tourId[0] : String(pageErrors.tourId);
                    }
                    // Check for general error message
                    else if (pageErrors.message) {
                        errorMessage = String(pageErrors.message);
                    }
                }
                
                error('Failed to Add Vehicle', errorMessage);
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
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100">
                        <AlertTriangle class="h-5 w-5 text-amber-600" />
                    </div>
                    <div>
                        <DialogTitle class="text-xl">Add New Vehicle</DialogTitle>
                        <DialogDescription class="mt-1">
                            Are you sure you want to add a new vehicle to this tour?
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>

            <div class="py-4">
                <div class="rounded-lg border bg-muted p-4 space-y-2">
                    <p class="text-sm font-medium text-foreground">Tour Information:</p>
                    <p class="text-sm text-muted-foreground">
                        <strong>Tour:</strong> {{ tourTitle || `Tour #${tourId}` }}
                    </p>
                </div>
                
                <div class="mt-4 rounded-lg border bg-blue-50 p-4">
                    <p class="text-sm text-blue-900">
                        <strong>Note:</strong> A new vehicle will be created with <strong>6 seats</strong> automatically. This action cannot be undone.
                    </p>
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
                    @click="handleConfirm" 
                    class="flex-1"
                    :disabled="processing"
                >
                    <span v-if="processing">Adding...</span>
                    <span v-else>Confirm & Add Vehicle</span>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

