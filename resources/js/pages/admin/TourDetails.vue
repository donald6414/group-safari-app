<script setup lang="ts">
import { ref, computed, unref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { adminTourShow } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import StartCard from '@/components/custom/StartCard.vue';
import { type Stat } from '@/types/stat';
import { type Tour, TourVehicleSeat, TourVehicle } from '@/types/tour';
import SeatBookingDrawer from '@/components/custom/drawer/SeatBooking.vue';
import AddVehicleConfirmation from '@/components/custom/modal/AddVehicleConfirmation.vue';
import DeleteVehicleConfirmation from '@/components/custom/modal/DeleteVehicleConfirmation.vue';
import { Toaster } from '@/components/ui/toast';
import { Button } from '@/components/ui/button';
import { Plus, Trash2 } from 'lucide-vue-next';

const props = defineProps<{
    id: number | string;
    responseData: {
        stats: Stat[];
        tour: Tour;
    };
}>();

const tourId = computed(() => {
    return typeof props.id === 'string' ? parseInt(props.id, 10) : props.id;
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tour Details',
        href: adminTourShow(tourId.value).url,
    },
];

// Drawer state
const isDrawerOpen = ref(false);
const selectedSeat = ref<TourVehicleSeat | null>(null);

// Add vehicle modal state
const isAddVehicleModalOpen = ref(false);

// Delete vehicle modal state
const isDeleteVehicleModalOpen = ref(false);
const selectedVehicleToDelete = ref<TourVehicle | null>(null);

// Format date for display
const formatDate = (dateString: string | null) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

// Handle seat click - opens drawer to show booking details
const handleSeatClick = (seat: TourVehicleSeat) => {
    selectedSeat.value = seat;
    isDrawerOpen.value = true;
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

// Handle drawer close
const handleDrawerClose = () => {
    isDrawerOpen.value = false;
    // Clear selection after a short delay to allow animation
    setTimeout(() => {
        selectedSeat.value = null;
    }, 300);
};

// Computed properties for drawer props
const drawerOpen = computed(() => isDrawerOpen.value);
const drawerSeat = computed(() => selectedSeat.value);

// Computed property for vehicle name
const vehicleToDeleteName = computed(() => {
    if (!selectedVehicleToDelete.value) return '';
    const vehicle = selectedVehicleToDelete.value;
    if (vehicle.vehicleType) return vehicle.vehicleType;
    const index = props.responseData.tour.tour_vehicles?.findIndex(v => v.id === vehicle.id);
    return index !== undefined && index >= 0 ? `Vehicle ${index + 1}` : `Vehicle #${vehicle.id}`;
});

// Handle add vehicle button click
const handleAddVehicleClick = () => {
    isAddVehicleModalOpen.value = true;
};

// Handle add vehicle modal close
const handleAddVehicleModalClose = () => {
    isAddVehicleModalOpen.value = false;
};

// Check if vehicle has reserved or booked seats
const hasReservedOrBookedSeats = (vehicle: TourVehicle): boolean => {
    if (!vehicle.tour_vehicle_seats || vehicle.tour_vehicle_seats.length === 0) {
        return false;
    }
    return vehicle.tour_vehicle_seats.some(seat => 
        seat.status === 'reserved' || seat.status === 'booked'
    );
};

// Check if vehicle can be deleted (not first vehicle and no reserved/booked seats)
const canDeleteVehicle = (vehicle: TourVehicle, vehicleIndex: number): boolean => {
    return vehicleIndex > 0 && !hasReservedOrBookedSeats(vehicle);
};

// Handle delete vehicle button click
const handleDeleteVehicleClick = (vehicle: TourVehicle) => {
    selectedVehicleToDelete.value = vehicle;
    isDeleteVehicleModalOpen.value = true;
};

// Handle delete vehicle modal close
const handleDeleteVehicleModalClose = () => {
    isDeleteVehicleModalOpen.value = false;
    setTimeout(() => {
        selectedVehicleToDelete.value = null;
    }, 300);
};
</script>

<template>
    <Head title="Tour Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="grid gap-4 md:grid-cols-4">
                <StartCard
                    v-for="(item, index) in props.responseData.stats"
                    :key="index"
                    :title="item.title"
                    :value="item.value"
                />
            </div>
            
            <!-- Tour Title Section -->
            <div class="mb-2">
                <h1 class="text-4xl font-bold text-foreground">{{ props.responseData.tour.title }}</h1>
                <p class="text-sm text-muted-foreground mt-1">Tour Package</p>
            </div>
            
            <!-- Tour Information Card -->
            <div class="rounded-xl border bg-card p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3 flex-1">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">START DATE</p>
                            <p class="mt-1 text-lg font-semibold">{{ formatDate(props.responseData.tour.startDate) }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">END DATE</p>
                            <p class="mt-1 text-lg font-semibold">{{ formatDate(props.responseData.tour.endDate) }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">LANGUAGE</p>
                            <p class="mt-1 text-lg font-semibold">{{ props.responseData.tour.language || '-' }}</p>
                        </div>
                    </div>
                    <Button @click="handleAddVehicleClick" class="ml-4 shrink-0">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Vehicle
                    </Button>
                </div>
            </div>
            
            <!-- Vehicles and Seats Section -->
            <div v-if="props.responseData.tour.tour_vehicles && props.responseData.tour.tour_vehicles.length > 0">
                <div v-for="(vehicle, vehicleIndex) in props.responseData.tour.tour_vehicles" :key="vehicle.id" class="mb-8">
                    <!-- Vehicle Header -->
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold">
                                {{ vehicle.vehicleType || `Vehicle ${vehicleIndex + 1}` }}
                            </h2>
                            <p class="text-sm text-muted-foreground">
                                {{ vehicle.numberOfSeat || 0 }} seats available
                            </p>
                        </div>
                        <Button 
                            v-if="canDeleteVehicle(vehicle, vehicleIndex)"
                            variant="destructive"
                            size="sm"
                            @click="handleDeleteVehicleClick(vehicle)"
                            class="shrink-0"
                        >
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete Vehicle
                        </Button>
                    </div>

                    <!-- Seats Grid -->
                    <div class="mb-4">
                        <h3 class="text-xl font-semibold mb-4">Seats Overview</h3>

                        <div v-if="vehicle.tour_vehicle_seats && vehicle.tour_vehicle_seats.length > 0"
                            class="grid grid-cols-3 gap-4 rounded-lg border p-6 md:grid-cols-4 lg:grid-cols-6">
                            <div 
                                v-for="seat in vehicle.tour_vehicle_seats" 
                                :key="seat.id" 
                                :class="[
                                    'flex flex-col items-center justify-center rounded-lg border-2 p-4 transition-all cursor-pointer',
                                    getSeatStatusColor(seat.status),
                                    'hover:scale-105 hover:shadow-md',
                                ]" 
                                @click="handleSeatClick(seat)"
                            >
                                <span class="text-2xl font-bold">{{ seat.seatNumber }}</span>
                                <span class="mt-2 text-xs font-medium">
                                    {{ getSeatStatusLabel(seat.status) }}
                                </span>
                                <span v-if="seat.bookings && seat.bookings.length > 0" class="mt-1 text-xs text-muted-foreground">
                                    {{ seat.bookings.length }} booking{{ seat.bookings.length > 1 ? 's' : '' }}
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

            <!-- Seat Booking Drawer -->
            <SeatBookingDrawer 
                v-if="drawerSeat"
                :open="unref(drawerOpen)"
                :seat="unref(drawerSeat)"
                :tour="props.responseData.tour"
                @update:open="handleDrawerClose"
            />
            
            <!-- Add Vehicle Confirmation Modal -->
            <AddVehicleConfirmation
                :open="isAddVehicleModalOpen"
                :tour-id="tourId"
                :tour-title="props.responseData.tour.title"
                @update:open="handleAddVehicleModalClose"
            />
            
            <!-- Delete Vehicle Confirmation Modal -->
            <DeleteVehicleConfirmation
                v-if="selectedVehicleToDelete"
                :open="isDeleteVehicleModalOpen"
                :vehicle-id="selectedVehicleToDelete.id"
                :vehicle-name="vehicleToDeleteName"
                :tour-title="props.responseData.tour.title"
                @update:open="handleDeleteVehicleModalClose"
            />
            
            <!-- Toast Notifications -->
            <Toaster />
        </div>
    </AppLayout>
</template>
