export interface Client {
    id:number;
    userId:number | null;
    name:string | null;
    email:string | null;
    phone:string | null;
    gender:string | null;
    dateOfBirth:string | null;
    age:number | null;
    nationality:string | null;
    language:string | null;
    created_at: string;
    updated_at: string;
}

export interface Booking {
    id: number;
    clientId: number;
    tourVehicleSeatId: number;
    startDate: string | null;
    endDate: string | null;
    status: string | 'active' | 'inactive';
    paymentReceipt?: string | null;
    created_at: string;
    updated_at: string;
    reservationDueDate?: string | null;
    reservedAt?: string | null;
    paymentReceiptNumber?: string | null;
    client?: Client;
}

export interface TourVehicleSeat {
    id: number;
    tourVehicleId: number;
    seatNumber: number | null;
    status: string | 'available' | 'booked' | 'cancelled';
    created_at: string;
    updated_at: string;
    bookings?: Booking[];
}

export interface TourVehicle {
    id: number;
    tourId: number;
    vehicleType: string | null;
    numberOfVehicle: number | null;
    numberOfSeat: number | null;
    status: string | 'active' | 'inactive';
    created_at: string;
    updated_at: string;
    tour_vehicle_seats?: TourVehicleSeat[];
}

export interface Tour {
    id: number;
    userId: number;
    title: string;
    numberOfVehicle: number | null;
    numberOfSeat: number | null;
    noOfDays: number | null;
    startDate: string | null;
    endDate: string | null;
    status: string | 'active' | 'inactive';
    language: string | null;
    created_at: string;
    updated_at: string;
    tour_vehicles_count: number;
    tour_vehicles?: TourVehicle[];
}