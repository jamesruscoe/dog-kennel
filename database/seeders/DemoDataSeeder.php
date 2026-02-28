<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\CareLog;
use App\Models\Company;
use App\Models\Dog;
use App\Models\Owner;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('slug', 'pawstay-demo')->firstOrFail();

        $staff = User::where('role', UserRole::Staff->value)
            ->where('company_id', $company->id)
            ->get();

        if ($staff->isEmpty()) {
            $this->command->warn('No staff users found. Run StaffUserSeeder first.');
            return;
        }

        $staffIds = $staff->pluck('id')->toArray();

        // ── Create demo owners ────────────────────────────────────────────────

        $ownerData = [
            ['name' => 'Sarah Thompson', 'email' => 'sarah@owner.test', 'phone' => '07700 900001'],
            ['name' => 'James Wilson',   'email' => 'james@owner.test', 'phone' => '07700 900002'],
            ['name' => 'Emma Davies',    'email' => 'emma@owner.test',  'phone' => '07700 900003'],
            ['name' => 'Michael Brown',  'email' => 'mike@owner.test',  'phone' => '07700 900004'],
            ['name' => 'Lisa Chen',      'email' => 'lisa@owner.test',  'phone' => '07700 900005'],
        ];

        $owners = collect($ownerData)->map(function ($data) use ($company) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'              => $data['name'],
                    'password'          => Hash::make('password'),
                    'role'              => UserRole::Owner->value,
                    'company_id'        => $company->id,
                    'email_verified_at' => now(),
                ]
            );

            return Owner::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'company_id'              => $company->id,
                    'phone'                   => $data['phone'],
                    'address'                 => fake()->address(),
                    'emergency_contact_name'  => fake()->name(),
                    'emergency_contact_phone' => fake()->phoneNumber(),
                ]
            );
        });

        // ── Create dogs for each owner ────────────────────────────────────────

        $dogProfiles = [
            // Sarah's dogs
            ['owner' => 0, 'name' => 'Biscuit',  'breed' => 'Labrador Retriever',  'sex' => 'male',   'colour' => 'Golden',        'dob' => '2019-03-15'],
            ['owner' => 0, 'name' => 'Poppy',    'breed' => 'Cocker Spaniel',       'sex' => 'female', 'colour' => 'Brown',         'dob' => '2021-07-22'],
            // James's dog
            ['owner' => 1, 'name' => 'Zeus',     'breed' => 'German Shepherd',      'sex' => 'male',   'colour' => 'Black & Tan',   'dob' => '2020-11-03'],
            // Emma's dogs
            ['owner' => 2, 'name' => 'Daisy',    'breed' => 'Golden Retriever',     'sex' => 'female', 'colour' => 'Golden',        'dob' => '2022-01-10'],
            ['owner' => 2, 'name' => 'Archie',   'breed' => 'Border Collie',        'sex' => 'male',   'colour' => 'Black & White', 'dob' => '2018-06-30'],
            // Michael's dog
            ['owner' => 3, 'name' => 'Rocky',    'breed' => 'Rottweiler',           'sex' => 'male',   'colour' => 'Black & Tan',   'dob' => '2021-02-14'],
            // Lisa's dogs
            ['owner' => 4, 'name' => 'Chloe',    'breed' => 'French Bulldog',       'sex' => 'female', 'colour' => 'Brindle',       'dob' => '2020-08-05'],
            ['owner' => 4, 'name' => 'Teddy',    'breed' => 'Poodle',               'sex' => 'male',   'colour' => 'White',         'dob' => '2023-03-20'],
        ];

        $dogs = collect($dogProfiles)->map(function ($profile) use ($owners, $company) {
            $owner = $owners[$profile['owner']];
            return Dog::firstOrCreate(
                ['owner_id' => $owner->id, 'name' => $profile['name']],
                [
                    'company_id'            => $company->id,
                    'breed'                 => $profile['breed'],
                    'date_of_birth'         => $profile['dob'],
                    'sex'                   => $profile['sex'],
                    'neutered'              => true,
                    'weight_kg'             => fake()->randomFloat(1, 5, 40),
                    'colour'                => $profile['colour'],
                    'microchip_number'      => fake()->numerify('###############'),
                    'vet_name'              => 'Riverside Veterinary Practice',
                    'vet_phone'             => '01234 567890',
                    'vaccination_confirmed' => true,
                    'dietary_notes'         => fake()->optional(0.5)->sentence(),
                ]
            );
        });

        // ── Create bookings in various states ─────────────────────────────────

        $today = Carbon::today();

        $bookingDefinitions = [
            // Currently boarding — approved, ongoing
            ['dog' => 0, 'in' => -2,  'out' => +3,  'status' => BookingStatus::Approved,  'care_logs' => true],
            ['dog' => 2, 'in' => -1,  'out' => +4,  'status' => BookingStatus::Approved,  'care_logs' => true],
            ['dog' => 4, 'in' => -3,  'out' => +1,  'status' => BookingStatus::Approved,  'care_logs' => true],

            // Upcoming — approved
            ['dog' => 1, 'in' => +5,  'out' => +10, 'status' => BookingStatus::Approved,  'care_logs' => false],
            ['dog' => 6, 'in' => +7,  'out' => +12, 'status' => BookingStatus::Approved,  'care_logs' => false],

            // Pending approval
            ['dog' => 3, 'in' => +14, 'out' => +18, 'status' => BookingStatus::Pending,   'care_logs' => false],
            ['dog' => 5, 'in' => +20, 'out' => +22, 'status' => BookingStatus::Pending,   'care_logs' => false],
            ['dog' => 7, 'in' => +3,  'out' => +5,  'status' => BookingStatus::Pending,   'care_logs' => false],

            // Historical — completed
            ['dog' => 0, 'in' => -30, 'out' => -25, 'status' => BookingStatus::Completed, 'care_logs' => false],
            ['dog' => 2, 'in' => -20, 'out' => -16, 'status' => BookingStatus::Completed, 'care_logs' => false],
            ['dog' => 1, 'in' => -45, 'out' => -40, 'status' => BookingStatus::Completed, 'care_logs' => false],

            // Cancelled / rejected
            ['dog' => 3, 'in' => +2,  'out' => +5,  'status' => BookingStatus::Cancelled, 'care_logs' => false],
            ['dog' => 6, 'in' => -5,  'out' => -2,  'status' => BookingStatus::Rejected,  'care_logs' => false],
        ];

        foreach ($bookingDefinitions as $def) {
            $dog      = $dogs[$def['dog']];
            $checkIn  = $today->copy()->addDays($def['in'])->toDateString();
            $checkOut = $today->copy()->addDays($def['out'])->toDateString();
            $nights   = abs($def['out'] - $def['in']);

            $booking = Booking::create([
                'company_id'           => $company->id,
                'dog_id'               => $dog->id,
                'check_in_date'        => $checkIn,
                'check_out_date'       => $checkOut,
                'status'               => $def['status']->value,
                'notes'                => fake()->optional(0.4)->sentence(),
                'special_requirements' => fake()->optional(0.2)->sentence(),
                'rejection_reason'     => $def['status'] === BookingStatus::Rejected ? 'No availability on requested dates.' : null,
                'cancellation_reason'  => $def['status'] === BookingStatus::Cancelled ? 'Owner cancelled the booking.' : null,
                'amount_pence'         => $nights * 3500,
                'payment_status'       => in_array($def['status'], [BookingStatus::Approved, BookingStatus::Completed]) ? 'pending' : null,
            ]);

            if ($def['care_logs']) {
                $this->seedCareLogsForBooking($booking, $staffIds, $company->id);
            }
        }
    }

    /**
     * Seed realistic care log entries for a currently-active booking.
     *
     * @param  int[]  $staffIds
     */
    private function seedCareLogsForBooking(Booking $booking, array $staffIds, int $companyId): void
    {
        $checkIn = Carbon::parse($booking->check_in_date);
        $today   = Carbon::today();

        $dailyActivities = [
            ['activity_type' => 'feeding',      'notes' => 'Morning meal — ate well.',          'hour' => 8],
            ['activity_type' => 'toilet',       'notes' => 'Morning toilet break in garden.',   'hour' => 9],
            ['activity_type' => 'walking',      'notes' => '30-minute walk — enjoyed the run.', 'hour' => 10],
            ['activity_type' => 'play',         'notes' => 'Garden playtime, very playful.',     'hour' => 12],
            ['activity_type' => 'feeding',      'notes' => 'Afternoon meal — eaten all food.',   'hour' => 17],
            ['activity_type' => 'toilet',       'notes' => 'Evening toilet break.',              'hour' => 18],
            ['activity_type' => 'health_check', 'notes' => 'Looking bright and happy.',          'hour' => 19],
        ];

        $period = Carbon::parse($checkIn)->daysUntil($today);

        foreach ($period as $date) {
            foreach ($dailyActivities as $activity) {
                CareLog::create([
                    'company_id'    => $companyId,
                    'booking_id'    => $booking->id,
                    'logged_by'     => fake()->randomElement($staffIds),
                    'activity_type' => $activity['activity_type'],
                    'notes'         => $activity['notes'],
                    'occurred_at'   => $date->copy()->setHour($activity['hour'])->setMinute(0),
                ]);
            }
        }
    }
}
