// ─────────────────────────────────────────────────────────────────────────────
// Domain types — mirrors the PHP Resource output shapes exactly.
// These are the canonical types used across all stores, services, and pages.
// ─────────────────────────────────────────────────────────────────────────────

export type UserRole = 'super_admin' | 'admin' | 'staff' | 'owner';

export type BookingStatus =
    | 'pending'
    | 'approved'
    | 'rejected'
    | 'cancelled'
    | 'completed';

export type ActivityType =
    | 'feeding'
    | 'walking'
    | 'medication'
    | 'grooming'
    | 'play'
    | 'toilet'
    | 'health_check'
    | 'other';

// ─────────────────────────────────────────────────────────────────────────────
// Auth
// ─────────────────────────────────────────────────────────────────────────────

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    role: UserRole;
    company_id: number | null;
}

// ─────────────────────────────────────────────────────────────────────────────
// Company
// ─────────────────────────────────────────────────────────────────────────────

export interface Company {
    id: number;
    name: string;
    slug: string;
    stripe_ready: boolean;
}

// ─────────────────────────────────────────────────────────────────────────────
// Owner
// ─────────────────────────────────────────────────────────────────────────────

export interface Owner {
    id: number;
    user_id: number;
    name: string;
    email: string;
    phone: string;
    address: string | null;
    emergency_contact_name: string | null;
    emergency_contact_phone: string | null;
    notes: string | null;
    dogs_count?: number;
    dogs?: Dog[];
    created_at: string;
    updated_at: string;
}

// ─────────────────────────────────────────────────────────────────────────────
// Dog
// ─────────────────────────────────────────────────────────────────────────────

export interface Dog {
    id: number;
    owner_id: number;
    owner?: Owner;
    name: string;
    breed: string;
    date_of_birth: string | null;
    age_years: number | null;
    sex: 'male' | 'female';
    neutered: boolean;
    weight_kg: number | null;
    colour: string | null;
    microchip_number: string | null;
    vet_name: string | null;
    vet_phone: string | null;
    vaccination_confirmed: boolean;
    medical_notes: string | null;
    dietary_notes: string | null;
    behavioural_notes: string | null;
    bookings_count?: number;
    created_at: string;
}

// ─────────────────────────────────────────────────────────────────────────────
// Booking
// ─────────────────────────────────────────────────────────────────────────────

export interface Booking {
    id: number;
    dog?: Dog;
    check_in_date: string;
    check_out_date: string;
    nights: number;
    status: BookingStatus;
    status_label: string;
    notes: string | null;
    special_requirements: string | null;
    rejection_reason: string | null;
    cancellation_reason: string | null;
    amount_pence: number | null;
    amount_display: string | null;
    payment_status: string | null;
    care_logs_count?: number;
    care_logs?: CareLog[];
    payment?: Payment | null;
    created_at: string;
    updated_at: string;
}

// ─────────────────────────────────────────────────────────────────────────────
// Care Log
// ─────────────────────────────────────────────────────────────────────────────

export interface CareLog {
    id: number;
    booking_id: number;
    booking?: Booking;
    logged_by: number;
    logged_by_user?: { id: number; name: string };
    activity_type: ActivityType;
    activity_label: string;
    notes: string | null;
    occurred_at: string;
    created_at: string;
}

// ─────────────────────────────────────────────────────────────────────────────
// Kennel Settings
// ─────────────────────────────────────────────────────────────────────────────

export interface KennelSettings {
    id: number;
    max_capacity: number;
    nightly_rate_pence: number;
    nightly_rate_display: string;
    operating_days: number[]; // DayOfWeek integer values (1=Mon ... 7=Sun)
    check_in_time: string; // HH:mm
    check_out_time: string; // HH:mm
    booking_lead_days: number;
    terms_and_conditions: string | null;
    updated_at: string;
}

// ─────────────────────────────────────────────────────────────────────────────
// Payment
// ─────────────────────────────────────────────────────────────────────────────

export interface Payment {
    id: number;
    booking_id: number;
    stripe_payment_id: string;
    amount_pence: number;
    amount_display: string;
    currency: string;
    status: string;
    paid_at: string;
}

// ─────────────────────────────────────────────────────────────────────────────
// Shared Inertia page props
// ─────────────────────────────────────────────────────────────────────────────

export interface SharedProps {
    auth: { user: AuthUser | null };
    flash: { success: string | null; error: string | null };
    company: Company | null;
    unread_notifications_count: number;
    csrf_token: string;
}

// ─────────────────────────────────────────────────────────────────────────────
// Paginator wrapper
// ─────────────────────────────────────────────────────────────────────────────

export interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

// ─────────────────────────────────────────────────────────────────────────────
// Calendar
// ─────────────────────────────────────────────────────────────────────────────

export interface CalendarDay {
    date: string; // Y-m-d
    isOperating: boolean;
    occupancy: number;
    capacity: number;
    isFull: boolean;
    isToday: boolean;
    isPast: boolean;
}

// ─────────────────────────────────────────────────────────────────────────────
// Notification
// ─────────────────────────────────────────────────────────────────────────────

export interface AppNotification {
    id: string;
    type: string;
    data: Record<string, unknown>;
    read_at: string | null;
    created_at: string;
}

// ─────────────────────────────────────────────────────────────────────────────
// Dashboard metrics
// ─────────────────────────────────────────────────────────────────────────────

export interface StaffMetrics {
    total_owners: number;
    total_dogs: number;
    active_bookings: number;
    pending_approval: number;
    in_stay_today: number;
    todays_checkins: number;
    todays_checkouts: number;
    revenue_display: string;
}
