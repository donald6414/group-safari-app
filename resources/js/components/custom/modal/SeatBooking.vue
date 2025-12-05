<script setup lang="ts">
import { ref, computed, watch, unref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Tour, TourVehicle, TourVehicleSeat } from '@/types/tour';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import InputError from '@/components/InputError.vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { Calendar } from '@/components/ui/calendar';
import { Calendar as CalendarIcon } from 'lucide-vue-next';
import { useToast } from '@/components/ui/toast';

// This component is for ADDING/NEW bookings only - not for updating existing bookings
const props = defineProps<{
    open: boolean;
    seat: TourVehicleSeat;
    vehicle: TourVehicle;
    tour: Tour;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean]; // This is Vue's v-model pattern for modal visibility, not for updating booking data
    'confirm': [data: {
        seatNumber: number;
        bookingType: 'full' | 'custom';
        startDate: string;
        endDate: string;
        client: {
            fullName: string;
            email?: string;
            phone?: string;
            gender?: string;
            nationality: string;
            language: string;
            maritalStatus?: string;
        };
    }];
}>();

// Booking type: 'full' or 'custom'
const bookingType = ref<'full' | 'custom'>('full');

// Custom dates - initialized with tour dates
const customStartDate = ref<string>('');
const customEndDate = ref<string>('');

// Date refs for calendar components (Date objects)
const customStartDateRef = ref<Date | null>(null);
const customEndDateRef = ref<Date | null>(null);

// Client information form fields
const fullName = ref<string>('');
const email = ref<string>('');
const phone = ref<string>('');
const gender = ref<string>('');
const nationality = ref<string>('');
const language = ref<string>('');
const maritalStatus = ref<string>('');

// Validation errors - ensure it's always an object
const errors = ref<Record<string, string>>({});

// Processing state
const processing = ref<boolean>(false);

// Toast notifications
const { success } = useToast();

// Computed properties for individual error fields (ensures safe access)
const fullNameError = computed(() => errors.value?.fullName || '');
const emailError = computed(() => errors.value?.email || '');
const nationalityError = computed(() => errors.value?.nationality || '');
const languageError = computed(() => errors.value?.language || '');

// Computed properties for Select model values (unwrapped for TypeScript)
const genderModel = computed(() => gender.value);
const languageModel = computed(() => language.value);
const maritalStatusModel = computed(() => maritalStatus.value);

// Handlers for booking type selection
const selectFullPackage = () => {
    bookingType.value = 'full';
};

const selectCustomDates = () => {
    bookingType.value = 'custom';
};

// Handlers for clearing errors on input
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

const clearNationalityError = () => {
    if (errors.value?.nationality) {
        delete errors.value.nationality;
    }
};

// Handlers for Select components
const handleGenderChange = (value: unknown) => {
    if (typeof value === 'string') {
        gender.value = value;
    }
};

const handleLanguageChange = (value: unknown) => {
    if (typeof value === 'string') {
        language.value = value;
        if (errors.value?.language) {
            delete errors.value.language;
        }
    }
};

const handleMaritalStatusChange = (value: unknown) => {
    if (typeof value === 'string') {
        maritalStatus.value = value;
    }
};

// Format date for HTML date input (YYYY-MM-DD)
const formatDateForInput = (dateString: string | null): string => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

// Convert string date to Date object
const stringToDate = (dateString: string | null): Date | null => {
    if (!dateString) return null;
    const date = new Date(dateString);
    return isNaN(date.getTime()) ? null : date;
};

// Computed properties for date display in calendar buttons
const customStartDateDisplay = computed(() => {
    if (customStartDateRef.value) {
        return customStartDateRef.value.toLocaleDateString();
    }
    return 'Pick a date';
});

const customEndDateDisplay = computed(() => {
    if (customEndDateRef.value) {
        return customEndDateRef.value.toLocaleDateString();
    }
    return 'Pick a date';
});

// Computed properties for min/max dates (YYYY-MM-DD format)
const tourStartDateFormatted = computed(() => formatDateForInput(props.tour.startDate));
const tourEndDateFormatted = computed(() => formatDateForInput(props.tour.endDate));

// Computed property for end date minimum (should be after start date or tour start date)
const customEndDateMin = computed(() => {
    if (customStartDateRef.value) {
        const nextDay = new Date(customStartDateRef.value);
        nextDay.setDate(nextDay.getDate() + 1);
        return nextDay.toISOString().split('T')[0];
    }
    if (props.tour.startDate) {
        const tourStart = new Date(props.tour.startDate);
        tourStart.setDate(tourStart.getDate() + 1);
        return tourStart.toISOString().split('T')[0];
    }
    return '';
});

// Handle custom start date change
const handleCustomStartDateChange = (date: Date | null) => {
    if (date) {
        // Check if date is within tour range
        const tourStart = props.tour.startDate ? new Date(props.tour.startDate) : null;
        const tourEnd = props.tour.endDate ? new Date(props.tour.endDate) : null;
        
        if (tourStart && date < tourStart) {
            // Date is before tour start, set to tour start
            date = tourStart;
        }
        if (tourEnd && date > tourEnd) {
            // Date is after tour end, set to tour end
            date = tourEnd;
        }
        
        // Normalize date to midnight
        const normalizedDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        customStartDateRef.value = normalizedDate;
        customStartDate.value = normalizedDate.toISOString().split('T')[0];
        
        // If end date is before or equal to new start date, reset end date
        if (customEndDateRef.value && customEndDateRef.value <= normalizedDate) {
            customEndDateRef.value = null;
            customEndDate.value = '';
        }
    } else {
        customStartDateRef.value = null;
        customStartDate.value = '';
    }
};

// Handle custom end date change
const handleCustomEndDateChange = (date: Date | null) => {
    if (date) {
        // Check if date is within tour range
        const tourStart = props.tour.startDate ? new Date(props.tour.startDate) : null;
        const tourEnd = props.tour.endDate ? new Date(props.tour.endDate) : null;
        
        if (tourStart && date < tourStart) {
            // Date is before tour start, set to tour start
            date = tourStart;
        }
        if (tourEnd && date > tourEnd) {
            // Date is after tour end, set to tour end
            date = tourEnd;
        }
        
        // Check if date is after start date
        if (customStartDateRef.value && date <= customStartDateRef.value) {
            // End date must be after start date
            const nextDay = new Date(customStartDateRef.value);
            nextDay.setDate(nextDay.getDate() + 1);
            if (tourEnd && nextDay > tourEnd) {
                // Can't set end date after start date if it exceeds tour end
                return;
            }
            date = nextDay;
        }
        
        // Normalize date to midnight
        const normalizedDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        customEndDateRef.value = normalizedDate;
        customEndDate.value = normalizedDate.toISOString().split('T')[0];
    } else {
        customEndDateRef.value = null;
        customEndDate.value = '';
    }
};

// Initialize custom dates when tour dates are available
watch(() => props.tour.startDate, (startDate) => {
    if (startDate) {
        customStartDate.value = formatDateForInput(startDate);
        customStartDateRef.value = stringToDate(startDate);
    } else {
        customStartDate.value = '';
        customStartDateRef.value = null;
    }
}, { immediate: true });

watch(() => props.tour.endDate, (endDate) => {
    if (endDate) {
        customEndDate.value = formatDateForInput(endDate);
        customEndDateRef.value = stringToDate(endDate);
    } else {
        customEndDate.value = '';
        customEndDateRef.value = null;
    }
}, { immediate: true });

// Format date for display
const formatDate = (dateString: string | null) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

// Simple function to calculate duration
const calculateDuration = (startDate: string, endDate: string): number => {
    if (!startDate || !endDate) return 0;
    const start = new Date(startDate);
    const end = new Date(endDate);
    return end.getDate() - start.getDate() + 1;
};

// Get full package duration
const getFullPackageDuration = (): number => {
    if (props.tour.startDate && props.tour.endDate) {
        return calculateDuration(props.tour.startDate, props.tour.endDate);
    }
    return 0;
};

// Get custom duration
const getCustomDuration = (): number => {
    if (customStartDate.value && customEndDate.value) {
        return calculateDuration(customStartDate.value, customEndDate.value);
    }
    return 0;
};

// Get booking start date
const getBookingStartDate = (): string => {
    return bookingType.value === 'full' 
        ? (props.tour.startDate || '') 
        : customStartDate.value;
};

// Get booking end date
const getBookingEndDate = (): string => {
    return bookingType.value === 'full' 
        ? (props.tour.endDate || '') 
        : customEndDate.value;
};

// Validate form
const validateForm = () => {
    // Ensure errors is always an object
    if (!errors.value) {
        errors.value = {};
    } else {
        errors.value = {};
    }
    let isValid = true;

    // Required fields
    if (!fullName.value.trim()) {
        errors.value.fullName = 'Full name is required';
        isValid = false;
    }
    if (!nationality.value.trim()) {
        errors.value.nationality = 'Nationality is required';
        isValid = false;
    }
    if (!language.value.trim()) {
        errors.value.language = 'Language is required';
        isValid = false;
    }

    // Email validation (if provided)
    if (email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
        errors.value.email = 'Please enter a valid email address';
        isValid = false;
    }

    return isValid;
};

// Handle confirm booking - creates a NEW booking (add only, no updates)
const handleConfirm = () => {
    if (!validateForm()) {
        return;
    }

    processing.value = true;

    // Prepare form data for submission
    const formData = {
        seatId: props.seat.id,
        vehicleId: props.vehicle.id,
        tourId: props.tour.id,
        bookingType: bookingType.value,
        startDate: getBookingStartDate(),
        endDate: getBookingEndDate(),
        // Client information
        fullName: fullName.value.trim(),
        email: email.value.trim() || null,
        phone: phone.value.trim() || null,
        gender: gender.value || null,
        nationality: nationality.value.trim(),
        language: language.value.trim(),
        maritalStatus: maritalStatus.value || null,
    };

    // Submit to backend
    router.post('/agent/book-seat', formData, {
        preserveScroll: true,
        onSuccess: () => {
            // Show success toast message
            success('Booking Successful', 'Seat has been reserved successfully. Please upload payment receipt to confirm your booking.');
            // Close modal and reset form on success
            handleClose();
            // Emit confirm event for parent component if needed
            emit('confirm', {
                seatNumber: props.seat.seatNumber || 0,
                bookingType: bookingType.value,
                startDate: getBookingStartDate(),
                endDate: getBookingEndDate(),
                client: {
                    fullName: fullName.value.trim(),
                    email: email.value.trim() || undefined,
                    phone: phone.value.trim() || undefined,
                    gender: gender.value || undefined,
                    nationality: nationality.value.trim(),
                    language: language.value.trim(),
                    maritalStatus: maritalStatus.value || undefined,
                },
            });
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

// Handle close - always resets form to initial state for new bookings
const handleClose = () => {
    emit('update:open', false);
    // Reset form to initial state (always starts fresh for new bookings)
    bookingType.value = 'full';
    if (props.tour.startDate) {
        customStartDate.value = formatDateForInput(props.tour.startDate);
        customStartDateRef.value = stringToDate(props.tour.startDate);
    } else {
        customStartDate.value = '';
        customStartDateRef.value = null;
    }
    if (props.tour.endDate) {
        customEndDate.value = formatDateForInput(props.tour.endDate);
        customEndDateRef.value = stringToDate(props.tour.endDate);
    } else {
        customEndDate.value = '';
        customEndDateRef.value = null;
    }
    // Reset client form fields
    fullName.value = '';
    email.value = '';
    phone.value = '';
    gender.value = '';
    nationality.value = '';
    language.value = '';
    maritalStatus.value = '';
    errors.value = {};
};

// Watch for modal open to reset form - ensures form is always fresh for new bookings
watch(() => props.open, (isOpen) => {
    if (!isOpen) {
        // Reset form to initial state (always starts fresh for new bookings)
        bookingType.value = 'full';
        if (props.tour.startDate) {
            customStartDate.value = formatDateForInput(props.tour.startDate);
            customStartDateRef.value = stringToDate(props.tour.startDate);
        } else {
            customStartDate.value = '';
            customStartDateRef.value = null;
        }
        if (props.tour.endDate) {
            customEndDate.value = formatDateForInput(props.tour.endDate);
            customEndDateRef.value = stringToDate(props.tour.endDate);
        } else {
            customEndDate.value = '';
            customEndDateRef.value = null;
        }
        // Reset client form fields
        fullName.value = '';
        email.value = '';
        phone.value = '';
        gender.value = '';
        nationality.value = '';
        language.value = '';
        maritalStatus.value = '';
        errors.value = {};
    }
});
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="max-w-md max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="mb-6">
                <DialogTitle class="text-2xl font-bold mb-2">
                    Book Seat {{ seat.seatNumber }}
                </DialogTitle>
                <DialogDescription class="text-sm">
                    Tour Duration: {{ formatDate(tour.startDate) }} to {{ formatDate(tour.endDate) }}
                </DialogDescription>
            </div>

            <!-- Booking Type Selection -->
            <div class="space-y-3 mb-8">
                <!-- Full Package Option -->
                <button
                    type="button"
                    @click="selectFullPackage"
                    :class="[
                        'w-full p-4 border-2 rounded-lg transition-all text-left',
                        bookingType === 'full' 
                            ? 'border-primary bg-primary/5' 
                            : 'border-border hover:border-border/80'
                    ]"
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-semibold text-foreground">Full Package</p>
                            <p class="text-sm text-muted-foreground">
                                Book for entire tour ({{ getFullPackageDuration() }} days)
                            </p>
                        </div>
                        <div
                            :class="[
                                'w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0',
                                bookingType === 'full' 
                                    ? 'border-primary bg-primary' 
                                    : 'border-border'
                            ]"
                        >
                            <div 
                                v-if="bookingType === 'full'"
                                class="w-2 h-2 bg-background rounded-full"
                            />
                        </div>
                    </div>
                </button>

                <!-- Custom Dates Option -->
                <button
                    type="button"
                    @click="selectCustomDates"
                    :class="[
                        'w-full p-4 border-2 rounded-lg transition-all text-left',
                        bookingType === 'custom' 
                            ? 'border-primary bg-primary/5' 
                            : 'border-border hover:border-border/80'
                    ]"
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-semibold text-foreground">Custom Dates</p>
                            <p class="text-sm text-muted-foreground">Choose specific travel dates</p>
                        </div>
                        <div
                            :class="[
                                'w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0',
                                bookingType === 'custom' 
                                    ? 'border-primary bg-primary' 
                                    : 'border-border'
                            ]"
                        >
                            <div 
                                v-if="bookingType === 'custom'"
                                class="w-2 h-2 bg-background rounded-full"
                            />
                        </div>
                    </div>
                </button>
            </div>

            <!-- Custom Date Inputs -->
            <div v-if="bookingType === 'custom'" class="space-y-4 mb-8 p-4 bg-card rounded-lg border border-border">
                <div class="space-y-2">
                    <Label>From Date</Label>
                    <Popover>
                        <PopoverTrigger as-child>
                            <Button
                                type="button"
                                variant="outline"
                                class="w-full justify-start text-left font-normal"
                            >
                                <CalendarIcon class="mr-2 h-4 w-4" />
                                <span>{{ customStartDateDisplay }}</span>
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent class="w-auto p-0" align="start">
                            <Calendar
                                :model-value="customStartDateRef"
                                @update:model-value="handleCustomStartDateChange"
                                :min="unref(tourStartDateFormatted) || undefined"
                                :max="unref(tourEndDateFormatted) || undefined"
                            />
                        </PopoverContent>
                    </Popover>
                </div>
                <div class="space-y-2">
                    <Label>To Date</Label>
                    <Popover>
                        <PopoverTrigger as-child>
                            <Button
                                type="button"
                                variant="outline"
                                class="w-full justify-start text-left font-normal"
                            >
                                <CalendarIcon class="mr-2 h-4 w-4" />
                                <span>{{ customEndDateDisplay }}</span>
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent class="w-auto p-0" align="start">
                            <Calendar
                                :model-value="customEndDateRef"
                                @update:model-value="handleCustomEndDateChange"
                                :min="(unref(customEndDateMin) || unref(tourStartDateFormatted)) || undefined"
                                :max="unref(tourEndDateFormatted) || undefined"
                            />
                        </PopoverContent>
                    </Popover>
                </div>
                <p v-if="getCustomDuration() > 0" class="text-xs text-muted-foreground">
                    Duration: {{ getCustomDuration() }} {{ getCustomDuration() === 1 ? 'day' : 'days' }}
                </p>
            </div>

            <!-- Client Information Form -->
            <div class="space-y-4 mb-6">
                <h3 class="text-lg font-semibold text-foreground">Client Information</h3>
                
                <div class="space-y-4">
                    <!-- Full Name (Required) -->
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
                        />
                        <InputError :message="fullNameError" />
                    </div>

                    <!-- Email (Optional) -->
                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            v-model="email"
                            type="email"
                            placeholder="Enter email address"
                            :class="emailError ? 'border-destructive' : ''"
                            @input="clearEmailError"
                        />
                        <InputError :message="emailError" />
                    </div>

                    <!-- Phone (Optional) -->
                    <div class="space-y-2">
                        <Label for="phone">Phone</Label>
                        <Input
                            id="phone"
                            v-model="phone"
                            type="tel"
                            placeholder="Enter phone number"
                        />
                    </div>

                    <!-- Gender (Optional) -->
                    <div class="space-y-2">
                        <Label for="gender">Gender</Label>
                        <Select :model-value="unref(genderModel)" @update:model-value="handleGenderChange">
                            <SelectTrigger id="gender">
                                <SelectValue placeholder="Select gender" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="male">Male</SelectItem>
                                <SelectItem value="female">Female</SelectItem>
                                <SelectItem value="other">Other</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Nationality (Required) -->
                    <div class="space-y-2">
                        <Label for="nationality">
                            Nationality <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            id="nationality"
                            v-model="nationality"
                            type="text"
                            placeholder="Enter nationality"
                            :class="nationalityError ? 'border-destructive' : ''"
                            @input="clearNationalityError"
                        />
                        <InputError :message="nationalityError" />
                    </div>

                    <!-- Language (Required) -->
                    <div class="space-y-2">
                        <Label for="language">
                            Language <span class="text-destructive">*</span>
                        </Label>
                        <Select :model-value="unref(languageModel)" @update:model-value="handleLanguageChange">
                            <SelectTrigger 
                                id="language"
                                :class="languageError ? 'border-destructive' : ''"
                            >
                                <SelectValue placeholder="Select language" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="ENGLISH">English</SelectItem>
                                <SelectItem value="SWAHILI">Swahili</SelectItem>
                                <SelectItem value="FRENCH">French</SelectItem>
                                <SelectItem value="SPANISH">Spanish</SelectItem>
                                <SelectItem value="GERMAN">German</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="languageError" />
                    </div>

                    <!-- Marital Status (Optional) -->
                    <div class="space-y-2">
                        <Label for="maritalStatus">Marital Status</Label>
                        <Select :model-value="unref(maritalStatusModel)" @update:model-value="handleMaritalStatusChange">
                            <SelectTrigger id="maritalStatus">
                                <SelectValue placeholder="Select marital status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="single">Single</SelectItem>
                                <SelectItem value="married">Married</SelectItem>
                                <SelectItem value="divorced">Divorced</SelectItem>
                                <SelectItem value="widowed">Widowed</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="p-4 bg-card rounded-lg border border-border mb-6">
                <p class="text-xs text-muted-foreground mb-2">Booking Summary</p>
                <div class="space-y-1">
                    <p class="text-sm font-medium text-foreground">Seat {{ seat.seatNumber }}</p>
                    <p class="text-sm text-muted-foreground">
                        {{ formatDate(getBookingStartDate()) }} to {{ formatDate(getBookingEndDate()) }}
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <DialogFooter class="flex gap-3">
                <Button 
                    variant="outline" 
                    @click="handleClose" 
                    class="flex-1 bg-transparent"
                    :disabled="processing"
                >
                    Cancel
                </Button>
                <Button 
                    @click="handleConfirm" 
                    class="flex-1"
                    :disabled="processing"
                >
                    <span v-if="processing">Processing...</span>
                    <span v-else>Confirm Booking</span>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
