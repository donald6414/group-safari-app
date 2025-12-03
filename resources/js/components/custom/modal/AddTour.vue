<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { adminTourCreate } from '@/routes';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
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
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { Calendar } from '@/components/ui/calendar';
import { Calendar as CalendarIcon } from 'lucide-vue-next';
import { type Highlight } from '@/types/highlights';

const props = defineProps<{
    open: boolean;
    highlights: Highlight[];
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

// Form data refs
const startDate = ref<string>('');
const endDate = ref<string>('');
const language = ref<string>('');
const selectedHighlights = ref<number[]>([]);

// Date refs for calendar components
const startDateRef = ref<Date | null>(null);
const endDateRef = ref<Date | null>(null);

// Validation errors from Laravel backend
const errors = ref<Record<string, string | string[]>>({});

// Loading state
const processing = ref(false);

const languages = [
    { value: 'ENGLISH', label: 'English' },
    { value: 'SWAHILI', label: 'Swahili' },
    { value: 'FRENCH', label: 'French' },
    { value: 'SPANISH', label: 'Spanish' },
    { value: 'GERMAN', label: 'German' },
];

// Get today's date in YYYY-MM-DD format for min attribute
const today = new Date().toISOString().split('T')[0];

// Computed properties for date display
const startDateDisplay = computed(() => {
    if (startDateRef.value) {
        return startDateRef.value.toLocaleDateString();
    }
    return 'Pick a date';
});

const endDateDisplay = computed(() => {
    if (endDateRef.value) {
        return endDateRef.value.toLocaleDateString();
    }
    return 'Pick a date';
});

// Get minimum date for end date calendar
const endDateMin = computed(() => {
    if (startDateRef.value) {
        const nextDay = new Date(startDateRef.value);
        nextDay.setDate(nextDay.getDate() + 1);
        return nextDay.toISOString().split('T')[0];
    }
    return today;
});

// Handle start date change
const handleStartDateChange = (date: Date | null) => {
    if (date) {
        // Normalize date to midnight
        const normalizedDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        startDateRef.value = normalizedDate;
        startDate.value = normalizedDate.toISOString().split('T')[0];
    } else {
        startDateRef.value = null;
        startDate.value = '';
    }
    
    // Clear error when user selects a date
    if (errors.value.startDate) {
        delete errors.value.startDate;
    }
    
    // Update end date min if start date is set
    if (date && endDateRef.value && endDateRef.value <= date) {
        endDateRef.value = null;
        endDate.value = '';
    }
};

// Handle end date change
const handleEndDateChange = (date: Date | null) => {
    if (date) {
        // Normalize date to midnight
        const normalizedDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        endDateRef.value = normalizedDate;
        endDate.value = normalizedDate.toISOString().split('T')[0];
    } else {
        endDateRef.value = null;
        endDate.value = '';
    }
    
    // Clear error when user selects a date
    if (errors.value.endDate) {
        delete errors.value.endDate;
    }
};

// Watch for language changes to clear errors
watch(language, () => {
    if (errors.value.language) {
        delete errors.value.language;
    }
});

// Watch for highlights changes to clear errors
watch(selectedHighlights, () => {
    if (selectedHighlights.value.length > 0 && errors.value.highlights) {
        delete errors.value.highlights;
    }
});

// Helper function to create v-model binding for highlight checkboxes
const getHighlightModel = (highlightId: number) => {
    return computed({
        get: () => selectedHighlights.value.includes(highlightId),
        set: (checked: boolean) => {
            if (checked) {
                if (!selectedHighlights.value.includes(highlightId)) {
                    selectedHighlights.value = [...selectedHighlights.value, highlightId];
                }
            } else {
                selectedHighlights.value = selectedHighlights.value.filter(id => id !== highlightId);
            }
        }
    });
};

// Get error message helper
const getErrorMessage = (field: string): string => {
    const error = errors.value[field];
    if (!error) return '';
    
    if (Array.isArray(error)) {
        return error[0] || '';
    }
    
    return typeof error === 'string' ? error : '';
};

// Check if field has error
const hasError = (field: string): boolean => {
    return !!errors.value[field];
};

// Handle form submission
const handleSubmit = () => {
    // Clear previous errors
    errors.value = {};
    processing.value = true;
    
    // Prepare form data
    const formData = {
        startDate: startDate.value,
        endDate: endDate.value,
        language: language.value,
        highlights: [...selectedHighlights.value],
    };
    
    // Submit using router.post
    router.post(adminTourCreate.url(), formData, {
        preserveScroll: true,
        onSuccess: () => {
            emit('update:open', false);
            resetForm();
        },
        onError: (pageErrors) => {
            // Handle Laravel validation errors
            // Laravel returns errors as { field: ['error message'] }
            const formattedErrors: Record<string, string | string[]> = {};
            
            Object.keys(pageErrors).forEach((key) => {
                const error = pageErrors[key];
                if (Array.isArray(error)) {
                    formattedErrors[key] = error;
                } else if (typeof error === 'string') {
                    formattedErrors[key] = error;
                }
            });
            
            errors.value = formattedErrors;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};

// Reset form
const resetForm = () => {
    startDate.value = '';
    endDate.value = '';
    language.value = '';
    selectedHighlights.value = [];
    startDateRef.value = null;
    endDateRef.value = null;
    errors.value = {};
    processing.value = false;
};

// Handle dialog open/close
const handleOpenChange = (open: boolean) => {
    emit('update:open', open);
    if (!open) {
        resetForm();
    }
};
</script>

<template>
    <Dialog :open="open" @update:open="handleOpenChange">
        <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Add New Tour</DialogTitle>
                <DialogDescription>
                    Create a new tour by filling in the details below.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-6">
                <!-- Date Range -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Start Date -->
                    <div class="space-y-2">
                        <Label for="startDate">Start Date</Label>
                        <Popover>
                            <PopoverTrigger as-child>
                                <Button
                                    id="startDate"
                                    type="button"
                                    variant="outline"
                                    :class="[
                                        'w-full justify-start text-left font-normal',
                                        hasError('startDate') ? 'border-destructive' : ''
                                    ]"
                                >
                                    <CalendarIcon class="mr-2 h-4 w-4" />
                                    <span>{{ startDateDisplay }}</span>
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent class="w-auto p-0" align="start">
                                <Calendar
                                    :model-value="startDateRef"
                                    @update:model-value="handleStartDateChange"
                                    :min="today"
                                />
                            </PopoverContent>
                        </Popover>
                        <InputError :message="getErrorMessage('startDate')" />
                    </div>

                    <!-- End Date -->
                    <div class="space-y-2">
                        <Label for="endDate">End Date</Label>
                        <Popover>
                            <PopoverTrigger as-child>
                                <Button
                                    id="endDate"
                                    type="button"
                                    variant="outline"
                                    :class="[
                                        'w-full justify-start text-left font-normal',
                                        hasError('endDate') ? 'border-destructive' : ''
                                    ]"
                                >
                                    <CalendarIcon class="mr-2 h-4 w-4" />
                                    <span>{{ endDateDisplay }}</span>
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent class="w-auto p-0" align="start">
                                <Calendar
                                    :model-value="endDateRef"
                                    @update:model-value="handleEndDateChange"
                                    :min="endDateMin as unknown as string"
                                />
                            </PopoverContent>
                        </Popover>
                        <InputError :message="getErrorMessage('endDate')" />
                    </div>
                </div>

                <!-- Language -->
                <div class="space-y-2">
                    <Label for="language">Language</Label>
                    <Select v-model="language">
                        <SelectTrigger 
                            id="language"
                            :class="hasError('language') ? 'border-destructive' : ''"
                        >
                            <SelectValue placeholder="Select language" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="lang in languages"
                                :key="lang.value"
                                :value="lang.value"
                            >
                                {{ lang.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="getErrorMessage('language')" />
                </div>

                <!-- Highlights -->
                <div class="space-y-2">
                    <Label>Highlights</Label>
                    <div
                        :class="[
                            'max-h-48 overflow-y-auto rounded-md border p-4 space-y-3',
                            hasError('highlights') ? 'border-destructive' : ''
                        ]"
                    >
                        <div
                            v-if="highlights.length === 0"
                            class="text-sm text-muted-foreground text-center py-4"
                        >
                            No highlights available
                        </div>
                        <div
                            v-for="highlight in highlights"
                            :key="highlight.id"
                            class="flex items-center space-x-2"
                        >
                            <Checkbox
                                :id="`highlight-${highlight.id}`"
                                v-model="getHighlightModel(highlight.id).value"
                            />
                            <Label
                                :for="`highlight-${highlight.id}`"
                                class="text-sm font-normal cursor-pointer flex-1"
                            >
                                {{ highlight.title }}
                            </Label>
                        </div>
                    </div>
                    <InputError :message="getErrorMessage('highlights')" />
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="handleOpenChange(false)"
                        :disabled="processing"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="processing">
                        {{ processing ? 'Creating...' : 'Create Tour' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
