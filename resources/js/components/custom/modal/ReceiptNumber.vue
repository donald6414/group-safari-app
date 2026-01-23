<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Receipt } from 'lucide-vue-next';
import { useToast } from '@/components/ui/toast';

const props = defineProps<{
    open: boolean;
    bookingId?: number;
    seatId?: number;
    seatNumber?: number | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'success': [];
}>();

// Toast notifications
const { success, error } = useToast();

// Receipt number input
const receiptNumber = ref<string>('');

// Validation errors
const errors = ref<Record<string, string>>({});

// Processing state
const processing = ref<boolean>(false);

// Computed property for receipt number error
const receiptNumberError = computed(() => errors.value?.paymentReceiptNumber || '');

// Validate form
const validateForm = () => {
    errors.value = {};
    let isValid = true;
    
    if (!receiptNumber.value || receiptNumber.value.trim() === '') {
        errors.value.paymentReceiptNumber = 'Receipt number is required';
        isValid = false;
    }
    
    return isValid;
};

// Handle form submission
const handleSubmit = () => {
    if (!validateForm()) {
        return;
    }
    
    if (!props.bookingId || !props.seatId) {
        error('Missing Information', 'Booking ID and Seat ID are required to confirm booking.');
        return;
    }
    
    processing.value = true;
    
    // Submit directly to the route
    router.post(
        '/agent/confirm-booking',
        {
            bookingId: props.bookingId,
            seatId: props.seatId,
            paymentReceiptNumber: receiptNumber.value.trim(),
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                success(
                    'Booking Confirmed',
                    'The booking has been confirmed and the seat status has been updated to booked.'
                );
                // Emit success event to parent
                emit('success');
                // Close modal and reset form
                handleClose();
            },
            onError: (pageErrors) => {
                // Handle Laravel validation errors
                errors.value = {};
                
                Object.keys(pageErrors).forEach((key) => {
                    const errorMsg = pageErrors[key];
                    if (Array.isArray(errorMsg)) {
                        errors.value[key] = errorMsg[0]; // Take first error message
                    } else if (typeof errorMsg === 'string') {
                        errors.value[key] = errorMsg;
                    }
                });
                
                // Show general error if no specific field errors
                if (Object.keys(errors.value).length === 0) {
                    const errorMessage = pageErrors?.message || pageErrors?.paymentReceiptNumber || 'Failed to confirm booking. Please try again.';
                    error('Confirmation Failed', errorMessage);
                }
            },
            onFinish: () => {
                processing.value = false;
            },
        }
    );
};

// Clear receipt number error
const clearReceiptNumberError = () => {
    if (errors.value && typeof errors.value === 'object' && 'paymentReceiptNumber' in errors.value) {
        delete errors.value.paymentReceiptNumber;
    }
};

// Handle close - resets form
const handleClose = () => {
    emit('update:open', false);
    // Reset form to initial state
    receiptNumber.value = '';
    errors.value = {};
    processing.value = false;
};

// Watch for modal open to reset form when closed
watch(() => props.open, (isOpen) => {
    if (!isOpen) {
        // Reset form when modal is closed
        receiptNumber.value = '';
        errors.value = {};
        processing.value = false;
    }
});
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10">
                        <Receipt class="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <DialogTitle class="text-2xl font-bold">
                            Payment Receipt Number
                        </DialogTitle>
                        <DialogDescription class="mt-1">
                            Enter the payment receipt number for booking #{{ bookingId || 'N/A' }}
                            <span v-if="seatNumber"> (Seat {{ seatNumber }})</span>.
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4 mt-4">
                <!-- Receipt Number Input -->
                <div class="space-y-2">
                    <Label for="receiptNumber">
                        Receipt Number <span class="text-destructive">*</span>
                    </Label>
                    <Input
                        id="receiptNumber"
                        v-model="receiptNumber"
                        type="text"
                        placeholder="Enter receipt number"
                        :disabled="processing"
                        class="w-full"
                        @input="clearReceiptNumberError"
                    />
                    <InputError :message="receiptNumberError" />
                </div>

                <DialogFooter class="flex gap-3 mt-6">
                    <Button
                        type="button"
                        variant="outline"
                        @click="handleClose"
                        :disabled="processing"
                        class="flex-1"
                    >
                        Cancel
                    </Button>
                    <Button
                        type="submit"
                        :disabled="processing || !receiptNumber || receiptNumber.trim() === ''"
                        class="flex-1"
                    >
                        <span v-if="processing">Submitting...</span>
                        <span v-else>Confirm Booking</span>
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
