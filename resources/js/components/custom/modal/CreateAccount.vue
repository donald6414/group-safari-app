<script setup lang="ts">
import { ref, watch, computed, unref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { useToast } from '@/components/ui/toast';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const props = defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

// Toast notifications
const { success } = useToast();

// Form fields
const fullName = ref<string>('');
const email = ref<string>('');
const phone = ref<string>('');

// Validation errors
const errors = ref<Record<string, string>>({});

// Computed properties for error messages
const fullNameError = computed(() => errors.value?.fullName || '');
const emailError = computed(() => errors.value?.email || '');
const phoneError = computed(() => errors.value?.phone || '');

// Processing state
const processing = ref<boolean>(false);

// Clear errors on input
const clearFullNameError = () => {
    if (errors.value?.fullName) {
        delete errors.value.fullName;
    }
};

const clearEmailError = () => {
    if (errors.value?.email) {
        delete errors.value.email;
    }
};

const clearPhoneError = () => {
    if (errors.value?.phone) {
        delete errors.value.phone;
    }
};

// Validate form
const validateForm = () => {
    errors.value = {};
    let isValid = true;

    // Required fields
    if (!fullName.value.trim()) {
        errors.value.fullName = 'Full name is required';
        isValid = false;
    }

    if (!email.value.trim()) {
        errors.value.email = 'Email is required';
        isValid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
        errors.value.email = 'Please enter a valid email address';
        isValid = false;
    }

    // Phone is optional, but if provided, validate format (basic validation)
    if (phone.value && phone.value.trim() && !/^[\d\s\-\+\(\)]+$/.test(phone.value)) {
        errors.value.phone = 'Please enter a valid phone number';
        isValid = false;
    }

    return isValid;
};

// Handle form submission
const handleSubmit = () => {
    if (!validateForm()) {
        return;
    }

    processing.value = true;

    // Prepare form data
    const formData = {
        name: fullName.value.trim(),
        email: email.value.trim(),
        phone: phone.value.trim() || null,
        role: 'agent',
    };

    // Submit to backend
    router.post('/admin/agent/create', formData, {
        preserveScroll: true,
        onSuccess: () => {
            // Show success toast
            success(
                'Invitation Sent',
                'Agent account has been created and invitation email has been sent successfully.'
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
    fullName.value = '';
    email.value = '';
    phone.value = '';
    errors.value = {};
    processing.value = false;
};

// Watch for modal open to reset form when closed
watch(() => props.open, (isOpen) => {
    if (!isOpen) {
        // Reset form when modal is closed
        fullName.value = '';
        email.value = '';
        phone.value = '';
        errors.value = {};
        processing.value = false;
    }
});
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle class="text-2xl font-bold">
                    Create New Agent Account
                </DialogTitle>
                <DialogDescription>
                    Fill in the details below to create a new agent account.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4 mt-4">
                <!-- Full Name -->
                <div class="space-y-2">
                    <Label for="fullName">
                        Full Name <span class="text-destructive">*</span>
                    </Label>
                    <Input
                        id="fullName"
                        v-model="fullName"
                        type="text"
                        placeholder="Enter full name"
                        :class="fullNameError ? 'border-destructive' : ''"
                        @input="clearFullNameError"
                        :disabled="processing"
                    />
                    <InputError :message="unref(fullNameError)" />
                </div>

                <!-- Email -->
                <div class="space-y-2">
                    <Label for="email">
                        Email <span class="text-destructive">*</span>
                    </Label>
                    <Input
                        id="email"
                        v-model="email"
                        type="email"
                        placeholder="Enter email address"
                        :class="emailError ? 'border-destructive' : ''"
                        @input="clearEmailError"
                        :disabled="processing"
                    />
                    <InputError :message="unref(emailError)" />
                </div>

                <!-- Phone (Optional) -->
                <div class="space-y-2">
                    <Label for="phone">Phone</Label>
                    <Input
                        id="phone"
                        v-model="phone"
                        type="tel"
                        placeholder="Enter phone number (optional)"
                        :class="phoneError ? 'border-destructive' : ''"
                        @input="clearPhoneError"
                        :disabled="processing"
                    />
                    <InputError :message="unref(phoneError)" />
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
                        :disabled="processing"
                        class="flex-1"
                    >
                        <span v-if="processing">Creating...</span>
                        <span v-else>Create Agent</span>
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

