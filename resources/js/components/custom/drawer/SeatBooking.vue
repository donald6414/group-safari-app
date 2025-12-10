<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { TourVehicleSeat, Booking, Client, Tour } from '@/types/tour';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { FileText, Image as ImageIcon, Eye, CheckCircle2, ChevronDown, ChevronUp, Calendar as CalendarIcon } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { useToast } from '@/components/ui/toast';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { Calendar } from '@/components/ui/calendar';

const props = defineProps<{
    open: boolean;
    seat: TourVehicleSeat | null;
    tour?: Tour | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

// Toast notifications
const { success, error } = useToast();

// Processing state for confirmation (track by booking ID)
const confirmingBookings = ref<Record<number, boolean>>({});

// Processing state for setting reservation due date (track by booking ID)
const settingDueDate = ref<Record<number, boolean>>({});

// Reservation due dates (track by booking ID)
const reservationDueDates = ref<Record<number, Date | null>>({});

// Open/closed state for accordion items (track by booking ID)
const openBookings = ref<Record<number, boolean>>({});

// Get all bookings
const bookings = computed<Booking[]>(() => {
    if (!props.seat?.bookings || props.seat.bookings.length === 0) {
        return [];
    }
    return props.seat.bookings;
});

// Check if seat is already booked
const isSeatBooked = computed(() => {
    return props.seat?.status === 'booked';
});

// Check if a booking can be confirmed
const canConfirmBooking = (booking: Booking) => {
    return (
        booking.status === 'active' &&
        // booking.paymentReceipt &&
        // booking.paymentReceipt.trim() !== '' &&
        props.seat?.status === 'reserved' &&
        !isSeatBooked.value
    );
};

// Check if booking has payment receipt
const hasPaymentReceipt = (booking: Booking) => {
    return booking.paymentReceipt && booking.paymentReceipt.trim() !== '';
};

// Check if receipt is an image (for a specific booking)
const isReceiptImage = (receiptPath: string | null | undefined) => {
    if (!receiptPath) return false;
    const receipt = receiptPath.toLowerCase();
    return receipt.endsWith('.jpg') || receipt.endsWith('.jpeg') || receipt.endsWith('.png') || receipt.endsWith('.gif');
};

// Check if booking accordion is open
const isBookingOpen = (bookingId: number) => {
    return openBookings.value[bookingId] || false;
};

// Payment receipt viewer state
const isReceiptViewerOpen = ref(false);
const viewingReceipt = ref<string | null>(null);

// Check if receipt is a PDF
const isReceiptPdf = (receiptPath: string | null | undefined) => {
    if (!receiptPath) return false;
    return receiptPath.toLowerCase().endsWith('.pdf');
};

// Get full URL for receipt
const getReceiptUrl = (receiptPath: string | null | undefined) => {
    if (!receiptPath) return '';
    // If it already starts with http or /, return as is
    if (receiptPath.startsWith('http') || receiptPath.startsWith('/')) {
        return receiptPath;
    }
    // Otherwise, prepend with / for relative path
    return receiptPath.startsWith('storage/') ? `/${receiptPath}` : `/${receiptPath}`;
};

// Open receipt viewer
const openReceiptViewer = (receiptPath: string) => {
    viewingReceipt.value = receiptPath;
    isReceiptViewerOpen.value = true;
};

// Close receipt viewer
const closeReceiptViewer = () => {
    isReceiptViewerOpen.value = false;
    viewingReceipt.value = null;
};

// Handle booking confirmation
const confirmBooking = (booking: Booking) => {
    if (!booking || !props.seat) {
        return;
    }

    confirmingBookings.value[booking.id] = true;

    router.post(
        '/agent/confirm-booking',
        {
            bookingId: booking.id,
            seatId: props.seat.id,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                success(
                    'Booking Confirmed',
                    'The booking has been confirmed and the seat status has been updated to booked.'
                );
                // Close the drawer after successful confirmation
                setTimeout(() => {
                    handleClose();
                }, 1500);
            },
            onError: (pageErrors) => {
                const errorMessage = pageErrors?.message || 'Failed to confirm booking. Please try again.';
                error('Confirmation Failed', errorMessage);
            },
            onFinish: () => {
                confirmingBookings.value[booking.id] = false;
            },
        }
    );
};

// Initialize reservation due dates from bookings
const initializeReservationDueDates = () => {
    bookings.value.forEach((booking) => {
        if ((booking as any).reservationDueDate) {
            reservationDueDates.value[booking.id] = new Date((booking as any).reservationDueDate);
        } else {
            // Initialize with null if not set
            if (!(booking.id in reservationDueDates.value)) {
                reservationDueDates.value[booking.id] = null;
            }
        }
    });
};

// Watch for changes in bookings and initialize reservation due dates
watch(bookings, () => {
    initializeReservationDueDates();
}, { immediate: true, deep: true });

// Handle reservation due date change
const handleDueDateChange = (bookingId: number, date: Date | null) => {
    reservationDueDates.value[bookingId] = date;
};

// Format date for display in input
const formatDateForInput = (date: Date | null) => {
    if (!date) return 'Select reservation due date';
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

// Get maximum date for reservation due date (tour end date)
const getMaxReservationDate = (booking: Booking) => {
    // Use tour end date if available, otherwise fallback to booking end date
    if (props.tour?.endDate) {
        return new Date(props.tour.endDate).toISOString().split('T')[0];
    }
    // Fallback to booking end date if tour is not provided
    if (booking.endDate) {
        return new Date(booking.endDate).toISOString().split('T')[0];
    }
    return undefined;
};

// Handle setting reservation due date
const setReservationDueDate = (booking: Booking) => {
    if (!booking || !reservationDueDates.value[booking.id]) {
        error('Invalid Date', 'Please select a valid reservation due date.');
        return;
    }

    const dueDate = reservationDueDates.value[booking.id];
    const maxDate = getMaxReservationDate(booking);
    
    // Validate that the selected date is not greater than the tour end date
    if (maxDate && dueDate) {
        const selectedDateStr = dueDate.toISOString().split('T')[0];
        if (selectedDateStr > maxDate) {
            error('Invalid Date', `Reservation due date cannot be greater than the tour end date (${formatDate(props.tour?.endDate || booking.endDate)}).`);
            return;
        }
    }

    settingDueDate.value[booking.id] = true;

    const formattedDate = dueDate?.toISOString().split('T')[0] || '';

    router.post(
        `/admin/set-reservation/due-date/${booking.id}`,
        {
            dueDate: formattedDate,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                success(
                    'Reservation Due Date Set',
                    'The reservation due date has been successfully updated.'
                );
            },
            onError: (pageErrors) => {
                const errorMessage = pageErrors?.message || pageErrors?.dueDate || 'Failed to set reservation due date. Please try again.';
                error('Update Failed', errorMessage);
            },
            onFinish: () => {
                settingDueDate.value[booking.id] = false;
            },
        }
    );
};

// Format date for display
const formatDate = (dateString: string | null) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

// Format date with time
const formatDateTime = (dateString: string | null) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Get seat status color
const getSeatStatusColor = (status: string) => {
    switch (status.toLowerCase()) {
        case 'available':
            return 'text-green-600 bg-green-50 border-green-200';
        case 'booked':
            return 'text-red-600 bg-red-50 border-red-200';
        case 'reserved':
            return 'text-yellow-600 bg-yellow-50 border-yellow-200';
        default:
            return 'text-gray-600 bg-gray-50 border-gray-200';
    }
};

// Get booking status color
const getBookingStatusColor = (status: string) => {
    switch (status.toLowerCase()) {
        case 'active':
            return 'text-green-600 bg-green-50 border-green-200';
        case 'confirmed_payment':
        case 'confirmed payment':
            return 'text-blue-600 bg-blue-50 border-blue-200';
        case 'inactive':
            return 'text-gray-600 bg-gray-50 border-gray-200';
        default:
            return 'text-gray-600 bg-gray-50 border-gray-200';
    }
};

// Handle close
const handleClose = () => {
    emit('update:open', false);
};
</script>

<template>
    <Sheet :open="open" @update:open="handleClose">
        <SheetContent class="w-full sm:max-w-2xl overflow-y-auto">
            <SheetHeader>
                <SheetTitle class="text-2xl font-bold">
                    Seat {{ seat?.seatNumber || 'N/A' }} Details
                </SheetTitle>
                <SheetDescription>
                    View complete booking information for this seat
                </SheetDescription>
            </SheetHeader>

            <div class="mt-6 space-y-6 mx-4">
                <!-- Seat Status -->
                <div class="rounded-lg border bg-card p-4">
                    <Label class="text-sm font-medium text-muted-foreground mb-2 block">
                        SEAT STATUS
                    </Label>
                    <div
                        v-if="seat"
                        :class="[
                            'inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold border',
                            getSeatStatusColor(seat.status),
                        ]"
                    >
                        {{ seat.status.toUpperCase() }}
                    </div>
                    <p v-else class="text-muted-foreground">No seat information available</p>
                </div>

                <!-- Bookings Accordion -->
                <div v-if="bookings.length > 0" class="space-y-3">
                    <h3 class="text-lg font-semibold">Bookings ({{ bookings.length }})</h3>
                    
                    <div class="space-y-2">
                        <Collapsible
                            v-for="booking in bookings"
                            :key="booking.id"
                            :open="openBookings[booking.id] || false"
                            @update:open="(value) => openBookings[booking.id] = value"
                            class="rounded-lg border bg-card overflow-hidden"
                        >
                            <CollapsibleTrigger
                                class="w-full flex items-center justify-between p-4 hover:bg-muted/50 transition-colors"
                            >
                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                    <div class="shrink-0">
                                        <div
                                            :class="[
                                                'inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold border',
                                                getBookingStatusColor(booking.status),
                                            ]"
                                        >
                                            {{ booking.status.toUpperCase() }}
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-semibold">Booking #{{ booking.id }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-xs text-muted-foreground">
                                                {{ formatDate(booking.startDate) }} - {{ formatDate(booking.endDate) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-xs text-muted-foreground hidden sm:inline">
                                        {{ formatDateTime(booking.created_at) }}
                                    </span>
                                    <ChevronDown
                                        :class="[
                                            'h-4 w-4 text-muted-foreground transition-transform',
                                            isBookingOpen(booking.id) ? 'rotate-180' : ''
                                        ]"
                                    />
                                </div>
                            </CollapsibleTrigger>
                            
                            <CollapsibleContent class="px-4 pb-4 space-y-4">
                                <!-- Booking Details -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                    <div>
                                        <Label class="text-sm font-medium text-muted-foreground mb-1 block">
                                            BOOKING ID
                                        </Label>
                                        <p class="text-base font-semibold">#{{ booking.id }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-muted-foreground mb-1 block">
                                            BOOKING DATE
                                        </Label>
                                        <p class="text-base font-semibold">{{ formatDateTime(booking.created_at) }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-muted-foreground mb-1 block">
                                            START DATE
                                        </Label>
                                        <p class="text-base font-semibold">{{ formatDate(booking.startDate) }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-muted-foreground mb-1 block">
                                            END DATE
                                        </Label>
                                        <p class="text-base font-semibold">{{ formatDate(booking.endDate) }}</p>
                                    </div>
                                </div>

                                <!-- Payment Receipt Section -->
                                <div v-if="hasPaymentReceipt(booking)" class="rounded-lg border bg-muted p-4 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-sm font-semibold">Payment Receipt</h4>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            @click="openReceiptViewer(booking.paymentReceipt!)"
                                            class="flex items-center gap-2"
                                        >
                                            <Eye class="h-4 w-4" />
                                            View
                                        </Button>
                                    </div>
                                    <div class="flex items-center gap-3 p-3 bg-background rounded-lg">
                                        <div v-if="isReceiptImage(booking.paymentReceipt)" class="flex items-center justify-center w-10 h-10 rounded-lg bg-primary/10">
                                            <ImageIcon class="h-5 w-5 text-primary" />
                                        </div>
                                        <div v-else class="flex items-center justify-center w-10 h-10 rounded-lg bg-primary/10">
                                            <FileText class="h-5 w-5 text-primary" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-medium">
                                                {{ isReceiptImage(booking.paymentReceipt) ? 'Image Receipt' : 'PDF Receipt' }}
                                            </p>
                                            <p class="text-xs text-muted-foreground truncate">
                                                {{ booking.paymentReceipt }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reservation Due Date Section -->
                                <div class="rounded-lg border bg-muted p-4 space-y-3">
                                    <h4 class="text-sm font-semibold">Reservation Due Date</h4>
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-2">
                                            <Popover>
                                                <PopoverTrigger as-child>
                                                    <Button
                                                        type="button"
                                                        variant="outline"
                                                        class="flex-1 justify-start text-left font-normal"
                                                        :disabled="settingDueDate[booking.id]"
                                                    >
                                                        <CalendarIcon class="mr-2 h-4 w-4" />
                                                        <span>{{ formatDateForInput(reservationDueDates[booking.id] || null) }}</span>
                                                    </Button>
                                                </PopoverTrigger>
                                                <PopoverContent class="w-auto p-0" align="start">
                                                    <Calendar
                                                        :model-value="reservationDueDates[booking.id] || null"
                                                        @update:model-value="(date) => handleDueDateChange(booking.id, date)"
                                                        :min="booking.startDate ? new Date(booking.startDate).toISOString().split('T')[0] : undefined"
                                                        :max="getMaxReservationDate(booking)"
                                                    />
                                                </PopoverContent>
                                            </Popover>
                                            <Button
                                                @click="setReservationDueDate(booking)"
                                                :disabled="!reservationDueDates[booking.id] || settingDueDate[booking.id]"
                                                size="default"
                                                class="shrink-0"
                                            >
                                                <span v-if="settingDueDate[booking.id]">Setting...</span>
                                                <span v-else>Set Date</span>
                                            </Button>
                                        </div>
                                        <div v-if="props.tour?.endDate || booking.endDate" class="text-xs text-muted-foreground">
                                            Maximum date: {{ formatDate(props.tour?.endDate || booking.endDate) }}
                                        </div>
                                        <div v-if="(booking as any).reservationDueDate" class="text-xs text-muted-foreground">
                                            Current due date: {{ formatDate((booking as any).reservationDueDate) }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Client Information -->
                                <div v-if="booking.client" class="rounded-lg border bg-muted p-4 space-y-3">
                                    <h4 class="text-sm font-semibold">Client Information</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div>
                                            <Label class="text-xs font-medium text-muted-foreground mb-1 block">
                                                FULL NAME
                                            </Label>
                                            <p class="text-sm font-semibold">{{ booking.client.name || '-' }}</p>
                                        </div>
                                        <div>
                                            <Label class="text-xs font-medium text-muted-foreground mb-1 block">
                                                EMAIL
                                            </Label>
                                            <p class="text-sm font-semibold">{{ booking.client.email || '-' }}</p>
                                        </div>
                                        <div>
                                            <Label class="text-xs font-medium text-muted-foreground mb-1 block">
                                                PHONE
                                            </Label>
                                            <p class="text-sm font-semibold">{{ booking.client.phone || '-' }}</p>
                                        </div>
                                        <div>
                                            <Label class="text-xs font-medium text-muted-foreground mb-1 block">
                                                GENDER
                                            </Label>
                                            <p class="text-sm font-semibold">
                                                {{ booking.client.gender ? booking.client.gender.charAt(0).toUpperCase() + booking.client.gender.slice(1) : '-' }}
                                            </p>
                                        </div>
                                        <div>
                                            <Label class="text-xs font-medium text-muted-foreground mb-1 block">
                                                NATIONALITY
                                            </Label>
                                            <p class="text-sm font-semibold">{{ booking.client.nationality || '-' }}</p>
                                        </div>
                                        <div>
                                            <Label class="text-xs font-medium text-muted-foreground mb-1 block">
                                                LANGUAGE
                                            </Label>
                                            <p class="text-sm font-semibold">{{ booking.client.language || '-' }}</p>
                                        </div>
                                        <div v-if="booking.client.dateOfBirth">
                                            <Label class="text-xs font-medium text-muted-foreground mb-1 block">
                                                DATE OF BIRTH
                                            </Label>
                                            <p class="text-sm font-semibold">{{ formatDate(booking.client.dateOfBirth) }}</p>
                                        </div>
                                        <div v-if="booking.client.age">
                                            <Label class="text-xs font-medium text-muted-foreground mb-1 block">
                                                AGE
                                            </Label>
                                            <p class="text-sm font-semibold">{{ booking.client.age }} years</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Confirm Booking Button -->
                                <div v-if="canConfirmBooking(booking)" class="pt-2 border-t">
                                    <Button
                                        @click="confirmBooking(booking)"
                                        :disabled="confirmingBookings[booking.id] || isSeatBooked"
                                        class="w-full flex items-center justify-center gap-2"
                                        size="lg"
                                    >
                                        <CheckCircle2 class="h-5 w-5" />
                                        <span v-if="confirmingBookings[booking.id]">Confirming...</span>
                                        <span v-else-if="isSeatBooked">Seat Already Booked</span>
                                        <span v-else>Confirm Booking & Payment</span>
                                    </Button>
                                </div>
                            </CollapsibleContent>
                        </Collapsible>
                    </div>
                </div>

                <!-- No Booking Message -->
                <div v-if="bookings.length === 0" class="rounded-lg border border-dashed border-muted-foreground/50 bg-muted/30 p-8 text-center">
                    <p class="text-muted-foreground text-lg font-medium">No Booking Found</p>
                    <p class="text-muted-foreground text-sm mt-2">
                        This seat is currently available and has no bookings.
                    </p>
                </div>
            </div>
        </SheetContent>
    </Sheet>

    <!-- Payment Receipt Viewer Dialog -->
    <Dialog :open="isReceiptViewerOpen" @update:open="closeReceiptViewer">
        <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle class="text-xl font-bold">
                    Payment Receipt
                </DialogTitle>
                <DialogDescription>
                    View the uploaded payment receipt
                </DialogDescription>
            </DialogHeader>

            <div class="mt-4">
                <!-- Image Display -->
                <div v-if="viewingReceipt && isReceiptImage(viewingReceipt)" class="w-full">
                    <img
                        :src="getReceiptUrl(viewingReceipt)"
                        alt="Payment receipt"
                        class="w-full h-auto rounded-lg border object-contain max-h-[70vh]"
                        @error="(e) => {
                            (e.target as HTMLImageElement).src = '/placeholder-image.png';
                        }"
                    />
                </div>

                <!-- PDF Display -->
                <div v-else-if="viewingReceipt && isReceiptPdf(viewingReceipt)" class="w-full">
                    <iframe
                        :src="getReceiptUrl(viewingReceipt)"
                        class="w-full h-[70vh] rounded-lg border"
                        frameborder="0"
                    >
                        <p class="p-4 text-center text-muted-foreground">
                            Your browser does not support PDFs. 
                            <a 
                                :href="getReceiptUrl(viewingReceipt)" 
                                target="_blank" 
                                class="text-primary underline"
                            >
                                Click here to download the PDF
                            </a>
                        </p>
                    </iframe>
                </div>

                <!-- Fallback for unknown file types -->
                <div v-else class="flex flex-col items-center justify-center p-8 text-center">
                    <FileText class="h-16 w-16 text-muted-foreground mb-4" />
                    <p class="text-muted-foreground mb-2">Unable to preview this file type</p>
                    <Button
                        variant="outline"
                        as-child
                    >
                        <a :href="getReceiptUrl(viewingReceipt)" target="_blank" class="flex items-center gap-2">
                            <FileText class="h-4 w-4" />
                            Download File
                        </a>
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>

