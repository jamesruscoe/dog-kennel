<?php

namespace Database\Seeders;

use App\Models\KennelSettings;
use Illuminate\Database\Seeder;

class KennelSettingsSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure only ever one settings row exists
        if (KennelSettings::exists()) {
            return;
        }

        KennelSettings::create([
            'max_capacity'         => 10,
            'nightly_rate_pence'   => 3500,          // £35.00/night
            'operating_days'       => [1, 2, 3, 4, 5, 6], // Mon–Sat
            'check_in_time'        => '10:00',
            'check_out_time'       => '16:00',
            'booking_lead_days'    => 1,
            'terms_and_conditions' => <<<EOT
Welcome to our kennel! By booking with us, you agree to the following:

1. All dogs must be up to date with vaccinations.
2. Dogs must be treated for fleas and ticks prior to boarding.
3. A cancellation fee applies for bookings cancelled within 48 hours.
4. We are not liable for any pre-existing health conditions.
5. Emergency veterinary costs are the owner's responsibility.

We look forward to looking after your dog!
EOT,
        ]);
    }
}
