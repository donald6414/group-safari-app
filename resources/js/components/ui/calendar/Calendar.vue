<script setup lang="ts">
import { ref, computed } from 'vue';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

const props = defineProps<{
    modelValue?: Date | null | any; // Allow any to handle refs/computed that Vue unwraps
    class?: string;
    min?: string; // YYYY-MM-DD format
    max?: string; // YYYY-MM-DD format
}>();

const emit = defineEmits<{
    'update:modelValue': [value: Date | null];
}>();

const currentMonth = ref(new Date());
const selectedDate = computed({
    get: () => {
        try {
            const value = props.modelValue;
            if (!value) return null;
            // Handle if value is a ref (shouldn't happen but just in case)
            if (value && typeof value === 'object' && 'value' in value) {
                const unwrapped = (value as any).value;
                if (unwrapped instanceof Date) return unwrapped;
                return null;
            }
            // Ensure we have a valid Date object
            if (value instanceof Date) {
                return value;
            }
            return null;
        } catch {
            return null;
        }
    },
    set: (value) => {
        try {
            emit('update:modelValue', value);
        } catch (error) {
            console.error('Error updating date:', error);
        }
    },
});

const daysInMonth = computed(() => {
    const year = currentMonth.value.getFullYear();
    const month = currentMonth.value.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const days: (Date | null)[] = [];
    
    // Add empty cells for days before the first day of the month
    for (let i = 0; i < firstDay.getDay(); i++) {
        days.push(null);
    }
    
    // Add all days of the month
    for (let day = 1; day <= lastDay.getDate(); day++) {
        days.push(new Date(year, month, day));
    }
    
    return days;
});

const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const isDateSelected = (date: Date) => {
    if (!selectedDate.value || !date) return false;
    try {
        return date.toDateString() === selectedDate.value.toDateString();
    } catch {
        return false;
    }
};

const isDateDisabled = (date: Date) => {
    const dateToCheck = new Date(date);
    dateToCheck.setHours(0, 0, 0, 0);
    
    if (props.min) {
        const minDate = new Date(props.min);
        minDate.setHours(0, 0, 0, 0);
        if (dateToCheck < minDate) return true;
    }
    
    if (props.max) {
        const maxDate = new Date(props.max);
        maxDate.setHours(0, 0, 0, 0);
        if (dateToCheck > maxDate) return true;
    }
    
    return false;
};

const handleDateClick = (date: Date) => {
    if (!date || isDateDisabled(date)) return;
    selectedDate.value = date;
};

const previousMonth = () => {
    currentMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() - 1, 1);
};

const nextMonth = () => {
    currentMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() + 1, 1);
};

const isToday = (date: Date) => {
    const today = new Date();
    return date.toDateString() === today.toDateString();
};
</script>

<template>
    <div :class="cn('p-3', props.class)">
        <div class="flex items-center justify-between mb-4">
            <Button variant="outline" size="icon" @click="previousMonth" class="h-7 w-7">
                <ChevronLeft class="h-4 w-4" />
            </Button>
            <div class="font-semibold">
                {{ monthNames[currentMonth.getMonth()] }} {{ currentMonth.getFullYear() }}
            </div>
            <Button variant="outline" size="icon" @click="nextMonth" class="h-7 w-7">
                <ChevronRight class="h-4 w-4" />
            </Button>
        </div>
        <div class="grid grid-cols-7 gap-1 mb-2">
            <div v-for="day in dayNames" :key="day" class="text-center text-sm font-medium text-muted-foreground p-1">
                {{ day }}
            </div>
        </div>
        <div class="grid grid-cols-7 gap-1">
            <template v-for="(date, index) in daysInMonth" :key="index">
                <button
                    v-if="date"
                    type="button"
                    @click="handleDateClick(date)"
                    :disabled="isDateDisabled(date)"
                    :class="cn(
                        'h-9 w-9 rounded-md text-sm transition-colors',
                        'hover:bg-accent hover:text-accent-foreground',
                        isDateSelected(date) && 'bg-primary text-primary-foreground',
                        isDateDisabled(date) && 'opacity-50 cursor-not-allowed',
                        isToday(date) && !isDateSelected(date) && 'border border-primary',
                    )"
                >
                    {{ date.getDate() }}
                </button>
                <div v-else class="h-9 w-9" />
            </template>
        </div>
    </div>
</template>

