<script setup lang="ts">
import { computed } from 'vue';
import { TourVehicleSeat, Booking, Client } from '@/types/tour';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    open: boolean;
    seat: TourVehicleSeat | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

// Get all bookings
const bookings = computed<Booking[]>(() => {
    if (!props.seat?.bookings || props.seat.bookings.length === 0) {
        return [];
    }
    return props.seat.bookings;
});

// Get the most recent/active booking
const activeBooking = computed<Booking | null>(() => {
    if (bookings.value.length === 0) {
        return null;
    }
    // Find active booking first, otherwise return the most recent
    const active = bookings.value.find(b => b.status === 'active');
    return active || bookings.value[0] || null;
});

// Get client from active booking
const client = computed<Client | null>(() => {
    return activeBooking.value?.client || null;
});

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

                <!-- Active Booking Information -->
                <div v-if="activeBooking" class="rounded-lg border bg-card p-6 space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Active Booking Information</h3>
                        <div
                            :class="[
                                'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border',
                                getBookingStatusColor(activeBooking.status),
                            ]"
                        >
                            {{ activeBooking.status.toUpperCase() }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground mb-1 block">
                                BOOKING ID
                            </Label>
                            <p class="text-base font-semibold">#{{ activeBooking.id }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground mb-1 block">
                                BOOKING DATE
                            </Label>
                            <p class="text-base font-semibold">{{ formatDateTime(activeBooking.created_at) }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground mb-1 block">
                                START DATE
                            </Label>
                            <p class="text-base font-semibold">{{ formatDate(activeBooking.startDate) }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground mb-1 block">
                                END DATE
                            </Label>
                            <p class="text-base font-semibold">{{ formatDate(activeBooking.endDate) }}</p>
                        </div>
                    </div>
                </div>

                <!-- All Bookings History -->
                <div v-if="bookings.length > 1" class="rounded-lg border bg-card p-6 space-y-4">
                    <h3 class="text-lg font-semibold">Booking History ({{ bookings.length }} total)</h3>
                    <div class="space-y-3">
                        <div
                            v-for="bookingItem in bookings"
                            :key="bookingItem.id"
                            class="rounded-lg border p-4 space-y-2"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold">Booking #{{ bookingItem.id }}</span>
                                    <div
                                        :class="[
                                            'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border',
                                            getBookingStatusColor(bookingItem.status),
                                        ]"
                                    >
                                        {{ bookingItem.status.toUpperCase() }}
                                    </div>
                                </div>
                                <span class="text-xs text-muted-foreground">
                                    {{ formatDateTime(bookingItem.created_at) }}
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div>
                                    <span class="text-muted-foreground">Start: </span>
                                    <span class="font-medium">{{ formatDate(bookingItem.startDate) }}</span>
                                </div>
                                <div>
                                    <span class="text-muted-foreground">End: </span>
                                    <span class="font-medium">{{ formatDate(bookingItem.endDate) }}</span>
                                </div>
                            </div>
                            <div v-if="bookingItem.client" class="pt-2 border-t">
                                <span class="text-sm text-muted-foreground">Client: </span>
                                <span class="text-sm font-medium">{{ bookingItem.client.name || 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Client Information -->
                <div v-if="activeBooking && client" class="rounded-lg border bg-card p-6 space-y-6">
                    <h3 class="text-lg font-semibold">Client Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground mb-1 block">
                                FULL NAME
                            </Label>
                            <p class="text-base font-semibold">{{ client.name || '-' }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground mb-1 block">
                                EMAIL
                            </Label>
                            <p class="text-base font-semibold">{{ client.email || '-' }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground mb-1 block">
                                PHONE
                            </Label>
                            <p class="text-base font-semibold">{{ client.phone || '-' }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground mb-1 block">
                                GENDER
                            </Label>
                            <p class="text-base font-semibold">
                                {{ client.gender ? client.gender.charAt(0).toUpperCase() + client.gender.slice(1) : '-' }}
                            </p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground mb-1 block">
                                NATIONALITY
                            </Label>
                            <p class="text-base font-semibold">{{ client.nationality || '-' }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground mb-1 block">
                                LANGUAGE
                            </Label>
                            <p class="text-base font-semibold">{{ client.language || '-' }}</p>
                        </div>
                        <div v-if="client.dateOfBirth">
                            <Label class="text-sm font-medium text-muted-foreground mb-1 block">
                                DATE OF BIRTH
                            </Label>
                            <p class="text-base font-semibold">{{ formatDate(client.dateOfBirth) }}</p>
                        </div>
                        <div v-if="client.age">
                            <Label class="text-sm font-medium text-muted-foreground mb-1 block">
                                AGE
                            </Label>
                            <p class="text-base font-semibold">{{ client.age }} years</p>
                        </div>
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
</template>

