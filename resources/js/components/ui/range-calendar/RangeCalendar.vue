<script setup lang="ts">
import { ref, computed } from 'vue';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

interface DateRange {
    start: Date | null;
    end: Date | null;
}

const props = defineProps<{
    modelValue?: DateRange | null;
    class?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: DateRange | null];
}>();

const currentMonth = ref(new Date());
const selectedRange = computed({
    get: () => props.modelValue || { start: null, end: null },
    set: (value) => emit('update:modelValue', value),
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

const isDateInRange = (date: Date) => {
    if (!selectedRange.value.start || !selectedRange.value.end) return false;
    return date >= selectedRange.value.start && date <= selectedRange.value.end;
};

const isDateSelected = (date: Date) => {
    if (!selectedRange.value.start && !selectedRange.value.end) return false;
    if (selectedRange.value.start && date.getTime() === selectedRange.value.start.getTime()) return true;
    if (selectedRange.value.end && date.getTime() === selectedRange.value.end.getTime()) return true;
    return false;
};

const handleDateClick = (date: Date) => {
    if (!selectedRange.value.start || (selectedRange.value.start && selectedRange.value.end)) {
        // Start new selection
        selectedRange.value = { start: date, end: null };
    } else if (selectedRange.value.start && !selectedRange.value.end) {
        // Complete the range
        if (date < selectedRange.value.start) {
            selectedRange.value = { start: date, end: selectedRange.value.start };
        } else {
            selectedRange.value = { start: selectedRange.value.start, end: date };
        }
    }
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
                    :class="cn(
                        'h-9 w-9 rounded-md text-sm transition-colors',
                        'hover:bg-accent hover:text-accent-foreground',
                        isDateSelected(date) && 'bg-primary text-primary-foreground',
                        isDateInRange(date) && !isDateSelected(date) && 'bg-accent/50',
                        isToday(date) && !isDateSelected(date) && 'border border-primary'
                    )"
                >
                    {{ date.getDate() }}
                </button>
                <div v-else class="h-9 w-9" />
            </template>
        </div>
    </div>
</template>

