<script setup lang="ts">
import { ref, watch, computed, unref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { useToast } from '@/components/ui/toast';
import { Upload, FileImage, X } from 'lucide-vue-next';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { TourVehicleSeat, Booking } from '@/types/tour';

const props = defineProps<{
    open: boolean;
    seat: TourVehicleSeat | null;
    booking: Booking | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

// Toast notifications
const { success, error } = useToast();

// File upload state
const selectedFile = ref<File | null>(null);
const filePreview = ref<string | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);

// Validation errors
const errors = ref<Record<string, string>>({});

// Computed properties for error messages
const fileError = computed(() => errors.value?.paymentReceipt || errors.value?.file || '');

// Processing state
const processing = ref<boolean>(false);

// Allowed file types
const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
const maxFileSize = 5 * 1024 * 1024; // 5MB

// Handle file selection
const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    
    if (!file) return;
    
    // Clear previous errors
    if (errors.value?.paymentReceipt) {
        delete errors.value.paymentReceipt;
    }
    if (errors.value?.file) {
        delete errors.value.file;
    }
    
    // Validate file type
    if (!allowedTypes.includes(file.type)) {
        errors.value.file = 'Please select a valid file (JPEG, PNG, GIF, or PDF)';
        return;
    }
    
    // Validate file size
    if (file.size > maxFileSize) {
        errors.value.file = 'File size must be less than 5MB';
        return;
    }
    
    selectedFile.value = file;
    
    // Create preview for images
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
            filePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    } else {
        filePreview.value = null;
    }
};

// Remove selected file
const removeFile = () => {
    selectedFile.value = null;
    filePreview.value = null;
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
    if (errors.value?.paymentReceipt) {
        delete errors.value.paymentReceipt;
    }
    if (errors.value?.file) {
        delete errors.value.file;
    }
};

// Validate form
const validateForm = () => {
    errors.value = {};
    let isValid = true;
    
    if (!selectedFile.value) {
        errors.value.paymentReceipt = 'Please select a payment receipt file';
        isValid = false;
    }
    
    return isValid;
};

// Handle form submission
const handleSubmit = () => {
    if (!validateForm() || !props.booking) {
        return;
    }
    
    processing.value = true;
    
    // Prepare form data with file upload
    const formData = new FormData();
    formData.append('bookingId', props.booking.id.toString());
    formData.append('paymentReceipt', selectedFile.value!);
    
    // Submit to backend
    router.post('/agent/upload-payment-receipt', formData, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            success(
                'Payment Receipt Uploaded',
                'Payment receipt has been uploaded successfully.'
            );
            // Close modal and reset form on success
            handleClose();
        },
        onError: (pageErrors) => {
            // Handle Laravel validation errors
            errors.value = {};
            
            Object.keys(pageErrors).forEach((key) => {
                const error = pageErrors[key];
                if (Array.isArray(error)) {
                    errors.value[key] = error[0]; // Take first error message
                } else if (typeof error === 'string') {
                    errors.value[key] = error;
                }
            });
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};

// Handle close - resets form
const handleClose = () => {
    emit('update:open', false);
    // Reset form to initial state
    selectedFile.value = null;
    filePreview.value = null;
    errors.value = {};
    processing.value = false;
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

// Watch for modal open to reset form when closed
watch(() => props.open, (isOpen) => {
    if (!isOpen) {
        // Reset form when modal is closed
        selectedFile.value = null;
        filePreview.value = null;
        errors.value = {};
        processing.value = false;
        if (fileInputRef.value) {
            fileInputRef.value.value = '';
        }
    }
});

// Get file name for display
const getFileName = () => {
    if (!selectedFile.value) return '';
    return selectedFile.value.name;
};

// Get file size for display
const getFileSize = () => {
    if (!selectedFile.value) return '';
    const sizeInMB = (selectedFile.value.size / (1024 * 1024)).toFixed(2);
    return `${sizeInMB} MB`;
};
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle class="text-2xl font-bold">
                    Upload Payment Receipt
                </DialogTitle>
                <DialogDescription>
                    Upload a payment receipt for seat {{ seat?.seatNumber || 'N/A' }}.
                    Accepted formats: JPEG, PNG, GIF, or PDF (max 5MB).
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4 mt-4">
                <!-- File Upload Area -->
                <div class="space-y-2">
                    <Label for="paymentReceipt">
                        Payment Receipt <span class="text-destructive">*</span>
                    </Label>
                    
                    <!-- File Input -->
                    <div
                        v-if="!selectedFile"
                        class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer hover:bg-accent transition-colors"
                        @click="unref(fileInputRef)?.click()"
                    >
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <Upload class="w-10 h-10 mb-3 text-muted-foreground" />
                            <p class="mb-2 text-sm text-muted-foreground">
                                <span class="font-semibold">Click to upload</span> or drag and drop
                            </p>
                            <p class="text-xs text-muted-foreground">
                                JPEG, PNG, GIF, or PDF (MAX. 5MB)
                            </p>
                        </div>
                        <input
                            ref="fileInputRef"
                            id="paymentReceipt"
                            type="file"
                            class="hidden"
                            accept="image/jpeg,image/jpg,image/png,image/gif,application/pdf"
                            @change="handleFileSelect"
                            :disabled="unref(processing)"
                        />
                    </div>
                    
                    <!-- Selected File Display -->
                    <div v-else class="space-y-3">
                        <!-- Image Preview -->
                        <div v-if="filePreview" class="relative w-full h-48 border rounded-lg overflow-hidden">
                            <img
                                :src="unref(filePreview) || ''"
                                alt="Payment receipt preview"
                                class="w-full h-full object-contain"
                            />
                            <Button
                                type="button"
                                variant="destructive"
                                size="icon"
                                class="absolute top-2 right-2"
                                @click="removeFile"
                                :disabled="processing"
                            >
                                <X class="h-4 w-4" />
                            </Button>
                        </div>
                        
                        <!-- File Info (for PDFs or when no preview) -->
                        <div v-else class="flex items-center gap-3 p-4 border rounded-lg bg-muted">
                            <FileImage class="h-8 w-8 text-muted-foreground" />
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium truncate">{{ getFileName() }}</p>
                                <p class="text-xs text-muted-foreground">{{ getFileSize() }}</p>
                            </div>
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                @click="removeFile"
                                :disabled="processing"
                            >
                                <X class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                    
                    <InputError :message="unref(fileError)" />
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
                        :disabled="processing || !unref(selectedFile)"
                        class="flex-1"
                    >
                        <span v-if="processing">Uploading...</span>
                        <span v-else>Upload Receipt</span>
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

