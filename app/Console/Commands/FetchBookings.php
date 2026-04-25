<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Position;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchBookings extends Command
{
    protected $signature = 'bookings:fetch-vatsim';

    protected $description = 'Fetch ATC bookings from VATSIM and import ITA subdivision bookings';

    public function handle(): int
    {
        $this->info('Fetching bookings from VATSIM...');

        $response = Http::timeout(15)->get('https://atc-bookings.vatsim.net/api/booking');

        if ($response->failed()) {
            $this->error("Failed to fetch bookings: HTTP {$response->status()}");

            return self::FAILURE;
        }

        $bookings = $response->json();

        if (! is_array($bookings)) {
            $this->error('Unexpected response format.');

            return self::FAILURE;
        }

        $italyBookings = collect($bookings)->filter(
            fn ($b) => isset($b['subdivision']) && $b['subdivision'] === 'ITA'
        );

        $this->info("Found {$italyBookings->count()} ITA booking(s).");

        $imported = 0;
        $skipped = 0;

        foreach ($italyBookings as $data) {
            $vatsimId = $data['id'];
            $userId = $data['cid'];

            // Skip if already imported (using cid + callsign + start as a natural unique key,
            // or store vatsim_id in your bookings table if you add that column)
            $exists = Booking::where('user_id', $data['cid'])
                ->where('callsign', $data['callsign'])
                ->where('time_start', $data['start'])
                ->exists();

            if ($exists) {
                $skipped++;

                continue;
            }

            // Resolve position by callsign if your positions table uses callsign
            $position = Position::where('callsign', $data['callsign'])->first();
            $user = User::where('id', $userId)->first();
            if (! $user) {
                $this->warn("Could not find position for callsign: {$data['callsign']} (VATSIM ID: {$userId})");
            }
            $isEvent = $data['type'] === 'event';
            $isTraining = $data['type'] === 'training';
            $isExam = $data['type'] === 'exam';

            Booking::create([
                'source' => 'API',
                'vatsim_booking' => $vatsimId,
                'callsign' => $data['callsign'],
                'position_id' => $position?->id,
                'user_id' => $data['cid'],
                'time_start' => $data['start'],
                'time_end' => $data['end'],
                'name' => $user->name,
                'training' => $isTraining,
                'event' => $isEvent,
                'exam' => $isExam,
            ]);

            $imported++;
        }

        $this->info("Done. Imported: {$imported}, Skipped (already exist): {$skipped}.");

        return self::SUCCESS;
    }
}
