<script setup lang="ts">
import { ref, computed, unref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { agentTourDetails } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { Tour, TourVehicle, TourVehicleSeat, Booking } from '@/types/tour';
import SeatBooking from '@/components/custom/modal/SeatBooking.vue';
import UploadPaymentReceipt from '@/components/custom/modal/UploadPaymentReceipt.vue';
import { Button } from '@/components/ui/button';
import { Plus } from 'lucide-vue-next';

const props = defineProps<{
    id: number | string;
    responseData: {
        tour: Tour;
    };
}>();

console.log(JSON.stringify(props.responseData, null, 2));

// Convert id to number if it's a string
const tourId = computed(() => {
    return typeof props.id === 'string' ? parseInt(props.id, 10) : props.id;
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tour Details',
        href: agentTourDetails(tourId.value).url,
    },
];

// Get current user
const page = usePage();
const currentUser = computed(() => {
    const auth = page.props.auth as { user?: { id: number } } | undefined;
    return auth?.user;
});

// Modal state
const isBookingModalOpen = ref(false);
const isPaymentReceiptModalOpen = ref(false);
const selectedSeat = ref<TourVehicleSeat | null>(null);
const selectedVehicle = ref<TourVehicle | null>(null);
const selectedBooking = ref<Booking | null>(null);

// Format date for display
const formatDate = (dateString: string | null) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

// Check if the current agent owns the reservation
const isReservedByCurrentAgent = (seat: TourVehicleSeat): boolean => {
    const user = currentUser.value;
    if (!user || seat.status !== 'reserved') {
        return false;
    }
    
    // Check if any booking for this seat belongs to the current agent
    if (seat.bookings && seat.bookings.length > 0) {
        return seat.bookings.some(booking => {
            // Check if the booking's client belongs to the current agent
            return booking.client?.userId === user.id;
        });
    }
    
    return false;
};

// Get the booking for the current agent's reservation
const getCurrentAgentBooking = (seat: TourVehicleSeat): Booking | null => {
    const user = currentUser.value;
    if (!user || seat.status !== 'reserved') {
        return null;
    }
    
    if (seat.bookings && seat.bookings.length > 0) {
        return seat.bookings.find(booking => {
            return booking.client?.userId === user.id;
        }) || null;
    }
    
    return null;
};

// Handle seat click - opens modal for NEW booking or payment receipt upload
const handleSeatClick = (seat: TourVehicleSeat, vehicle: TourVehicle) => {
    if (seat.status === 'available') {
        // Set selected seat and vehicle for new booking
        selectedSeat.value = seat;
        selectedVehicle.value = vehicle;
        isBookingModalOpen.value = true;
    } else if (seat.status === 'reserved' && isReservedByCurrentAgent(seat)) {
        // If reserved by current agent, open payment receipt upload modal
        const booking = getCurrentAgentBooking(seat);
        if (booking) {
            selectedSeat.value = seat;
            selectedVehicle.value = vehicle;
            selectedBooking.value = booking;
            isPaymentReceiptModalOpen.value = true;
        }
    }
};

// Get seat status color
const getSeatStatusColor = (status: string) => {
    switch (status.toLowerCase()) {
        case 'available':
            return 'bg-green-100 border-green-300 text-green-800';
        case 'booked':
            return 'bg-red-100 border-red-300 text-red-800';
        case 'reserved':
            return 'bg-yellow-100 border-yellow-300 text-yellow-800';
        default:
            return 'bg-gray-100 border-gray-300 text-gray-800';
    }
};

// Get seat status label
const getSeatStatusLabel = (status: string) => {
    return status.toUpperCase();
};

// Check if seat is clickable
const isSeatClickable = (seat: TourVehicleSeat) => {
    const status = seat.status.toLowerCase();
    if (status === 'available') {
        return true;
    }
    if (status === 'reserved' && isReservedByCurrentAgent(seat)) {
        return true;
    }
    return false;
};

// Handle booking modal close - clears selection for next new booking
const handleBookingModalClose = () => {
    isBookingModalOpen.value = false;
    // Clear selection to ensure next booking starts fresh
    selectedSeat.value = null;
    selectedVehicle.value = null;
};

// Handle payment receipt modal close
const handlePaymentReceiptModalClose = () => {
    isPaymentReceiptModalOpen.value = false;
    selectedSeat.value = null;
    selectedVehicle.value = null;
    selectedBooking.value = null;
};

// Handle booking confirmation
const handleBookingConfirm = (data: {
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
}) => {
    // TODO: Implement booking submission
    console.log('Booking confirmed:', {
        seatId: selectedSeat.value?.id,
        vehicleId: selectedVehicle.value?.id,
        tourId: tourId.value,
        ...data,
    });

    // Close modal after booking
    handleBookingModalClose();
};

// Computed properties for modal props (to ensure proper type unwrapping)
const bookingModalOpen = computed(() => isBookingModalOpen.value);
const paymentReceiptModalOpen = computed(() => isPaymentReceiptModalOpen.value);
const modalSeat = computed(() => selectedSeat.value);
const modalVehicle = computed(() => selectedVehicle.value);
const modalBooking = computed(() => selectedBooking.value);
const showBookingModal = computed(() => !!selectedSeat.value && !!selectedVehicle.value && isBookingModalOpen.value);
const showPaymentReceiptModal = computed(() => !!selectedSeat.value && !!selectedVehicle.value && !!selectedBooking.value && isPaymentReceiptModalOpen.value);
</script>

<template>

    <Head title="Tour Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <!-- Tour Title Section -->
            <div class="mb-2">
                <div class="flex flex-row items-center justify-between">
                    <div>
                        <h1 class="text-4xl font-bold text-foreground">{{ responseData.tour.title }}</h1>
                        <p class="text-sm text-muted-foreground mt-1">Tour Package</p>
                    </div>
                    <!-- <div>
                        <Button variant="outline">
                            <Plus />
                            Add Vehicle
                        </Button>
                    </div> -->
                </div>
            </div>

            <!-- Tour Information Card -->
            <div class="rounded-xl border bg-card p-6 shadow-sm">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">START DATE</p>
                        <p class="mt-1 text-lg font-semibold">{{ formatDate(responseData.tour.startDate) }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">END DATE</p>
                        <p class="mt-1 text-lg font-semibold">{{ formatDate(responseData.tour.endDate) }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">LANGUAGE</p>
                        <p class="mt-1 text-lg font-semibold">{{ responseData.tour.language || '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Vehicles and Seats Section -->
            <div v-if="responseData.tour.tour_vehicles && responseData.tour.tour_vehicles.length > 0">
                <div v-for="(vehicle, vehicleIndex) in responseData.tour.tour_vehicles" :key="vehicle.id" class="mb-8">
                    <!-- Vehicle Header -->
                    <div class="mb-4">
                        <h2 class="text-2xl font-bold">
                            {{ vehicle.vehicleType || `Vehicle ${vehicleIndex + 1}` }}
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            {{ vehicle.numberOfSeat || 0 }} seats available
                        </p>
                    </div>

                    <!-- Select Your Seat Section -->
                    <div class="mb-4">
                        <h3 class="text-xl font-semibold mb-4">Select Your Seat</h3>

                        <!-- Seats Grid -->
                        <div v-if="vehicle.tour_vehicle_seats && vehicle.tour_vehicle_seats.length > 0"
                            class="grid grid-cols-3 gap-4 rounded-lg border p-6 md:grid-cols-4 lg:grid-cols-6">
                            <div v-for="seat in vehicle.tour_vehicle_seats" :key="seat.id" :class="[
                                'flex flex-col items-center justify-center rounded-lg border-2 p-4 transition-all',
                                getSeatStatusColor(seat.status),
                                isSeatClickable(seat)
                                    ? 'cursor-pointer hover:scale-105 hover:shadow-md'
                                    : 'cursor-not-allowed opacity-75',
                            ]" @click="handleSeatClick(seat, vehicle)">
                                <span class="text-2xl font-bold">{{ seat.seatNumber }}</span>
                                <span class="mt-2 text-xs font-medium">
                                    {{ getSeatStatusLabel(seat.status) }}
                                </span>
                            </div>
                        </div>
                        <div v-else class="rounded-lg border p-8 text-center text-muted-foreground">
                            No seats available for this vehicle
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="flex flex-wrap gap-4 text-sm">
                        <div class="flex items-center gap-2">
                            <div class="h-4 w-4 rounded border-2 bg-green-100 border-green-300"></div>
                            <span>Available</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="h-4 w-4 rounded border-2 bg-red-100 border-red-300"></div>
                            <span>Booked</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="h-4 w-4 rounded border-2 bg-yellow-100 border-yellow-300"></div>
                            <span>Reserved</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- No Vehicles Message -->
            <div v-else class="rounded-lg border p-8 text-center text-muted-foreground">
                No vehicles available for this tour
            </div>

            <!-- Booking Modal -->
            <SeatBooking v-if="showBookingModal && modalSeat && modalVehicle"
                :key="`seat-${unref(modalSeat)?.id}-vehicle-${unref(modalVehicle)?.id}`" :open="unref(bookingModalOpen)"
                :seat="unref(modalSeat)!" :vehicle="unref(modalVehicle)!" :tour="responseData.tour"
                @update:open="handleBookingModalClose" @confirm="handleBookingConfirm" />
            
            <!-- Payment Receipt Upload Modal -->
            <UploadPaymentReceipt v-if="showPaymentReceiptModal && modalSeat && modalBooking"
                :key="`payment-receipt-${unref(modalBooking)?.id}`"
                :open="unref(paymentReceiptModalOpen)"
                :seat="unref(modalSeat)!"
                :booking="unref(modalBooking)!"
                @update:open="handlePaymentReceiptModalClose" />
        </div>
    </AppLayout>
</template>
