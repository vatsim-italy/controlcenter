<?php

namespace App\Jobs;

use App\Helpers\VatsimRating;
use App\Models\Rating;
use App\Models\RatingEligibility;
use App\Models\User;
use App\Services\StatisticsService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use anlutro\LaravelSettings\Facade as Setting;

class ComputeTierEligibility implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const TIERS = [
        'LIMC_X_GND/DEL' => [
            'position'      => 'gnd',
            'min_rating'    => VatsimRating::S1,
            'rating_months' => 2,
            'hours'         => 30,
            'depends_on'    => [],
        ],
        'LIMC_X_TWR' => [
            'position'      => 'twr',
            'min_rating'    => VatsimRating::S2,
            'rating_months' => 2,
            'hours'         => 30,
            'depends_on'    => ['LIMC_X_GND/DEL'],
        ],
        'LIMM_X_APP' => [
            'position'      => 'app',
            'min_rating'    => VatsimRating::S3,
            'rating_months' => 3,
            'hours'         => 50,
            'depends_on'    => ['LIMC_X_GND/DEL', 'LIMC_X_TWR'],
        ],
        'LIRF_X_GND/DEL' => [
            'position'      => 'gnd',
            'min_rating'    => VatsimRating::S1,
            'rating_months' => 2,
            'hours'         => 30,
            'depends_on'    => [],
        ],
        'LIRF_X_TWR' => [
            'position'      => 'twr',
            'min_rating'    => VatsimRating::S2,
            'rating_months' => 2,
            'hours'         => 30,
            'depends_on'    => ['LIRF_X_GND/DEL'],
        ],
        'LIRR_X_APP' => [
            'position'      => 'app',
            'min_rating'    => VatsimRating::S3,
            'rating_months' => 2,
            'hours'         => 30,
            'extra_hours'   => ['prefix' => 'LIRF_', 'required' => 10],
            'depends_on'    => ['LIRF_X_GND/DEL', 'LIRF_X_TWR'],
        ],
    ];

    private const POSITION_SUFFIXES = [
        'gnd' => ['_GND', '_DEL'],
        'twr' => ['_TWR'],
        'app' => ['_APP'],
    ];

    public function handle(StatisticsService $statisticsService): void
    {
        Log::info('ComputeTierEligibility: starting');

        $tierRatings = Rating::where('endorsement_type', 'T1')->get()->keyBy('name');

        if ($tierRatings->isEmpty()) {
            Log::warning('ComputeTierEligibility: no T1 ratings found, aborting.');
            return;
        }

        $allowedSubDivisions = array_map('trim', explode(',', Setting::get('trainingSubDivisions', '')));

        $to   = Carbon::now()->endOfDay();
        $from = (clone $to)->subMonths(12)->startOfDay(); 

        $processed = 0;
        $failed    = 0;

        User::where(function ($query) use ($allowedSubDivisions) {
                if (config('app.mode') === 'subdivision') {
                    $query->whereIn('subdivision', $allowedSubDivisions);
                } else {
                    $query->where('division', config('app.owner_code'));
                }
            })
            ->whereHas('atcActivity', fn ($q) => $q->where('atc_active', true))
            ->with(['trainings', 'endorsements.ratings'])
            ->chunkById(50, function ($users) use ($tierRatings, $statisticsService, $from, $to, &$processed, &$failed) {
                
                foreach ($users as $user) {
                    try {
                        $this->processUser($user, $tierRatings, $statisticsService, $from, $to);
                        $processed++;
                        usleep(200000); 

                    } catch (\Throwable $e) {
                        $failed++;
                        Log::error('ComputeTierEligibility: failed for user ' . $user->id, [
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

        Log::info('ComputeTierEligibility: done', ['processed' => $processed, 'failed' => $failed]);
    }

    private function processUser(
        User $user,
        \Illuminate\Support\Collection $tierRatings,
        StatisticsService $statisticsService,
        Carbon $from,
        Carbon $to
    ): void {
        $sessions = $statisticsService->getCachedAtcSessions((string) $user->id, $from, $to);

        $hoursCache = [];
        $prefixHoursCache = [];

        foreach (array_unique(array_column(self::TIERS, 'position')) as $position) {
            $hoursCache[$position] = $this->sumHoursByPositionType($sessions, self::POSITION_SUFFIXES[$position]);
        }

        foreach (self::TIERS as $config) {
            if (isset($config['extra_hours'])) {
                $prefix = $config['extra_hours']['prefix'];
                $prefixHoursCache[$prefix] ??= $this->sumHoursByPrefix($sessions, $prefix);
            }
        }

        Log::debug('ComputeTierEligibility: user stats', [
            'user_id'      => $user->id,
            'rating'       => $user->rating,
            'sessions'     => count($sessions),
            'hours'        => array_map(fn ($h) => round($h, 2), $hoursCache),
            'prefix_hours' => array_map(fn ($h) => round($h, 2), $prefixHoursCache),
        ]);

        $results = [];

        foreach (self::TIERS as $tierName => $config) {
            $results[$tierName] = $this->evaluateTier(
                $user,
                $tierName,
                $config,
                $hoursCache,
                $prefixHoursCache,
                $results,
                $tierRatings
            );
        }

        foreach ($results as $tierName => $result) {
            $rating = $tierRatings->get($tierName);
            if (! $rating) continue;

            $eligible = $result['eligible'];
            $reason = $result['reason'];

            // If they already own it, force eligible to true and clear/set the reason
            if ($user->hasEndorsementRating($rating)) {
                $eligible = true;
                $reason = 'Endorsement already held';
            }

            RatingEligibility::updateOrCreate(
                ['user_id' => $user->id, 'rating_id' => $rating->id],
                ['eligible' => $eligible, 'reason' => $reason]
            );
        }
    }

    private function evaluateTier(
        User $user,
        string $tierName,
        array $config,
        array $hoursCache,
        array $prefixHoursCache,
        array $previousResults,
        \Illuminate\Support\Collection $tierRatings
    ): array {
        $unmet = [];

        foreach ($config['depends_on'] as $dep) {
            $depRating = $tierRatings->get($dep);
            $holdsEndorsement = $depRating ? $user->hasEndorsementRating($depRating) : false;

            if (! ($previousResults[$dep]['eligible'] ?? false) && ! $holdsEndorsement) {
                $unmet[] = 'Must be eligible for or hold ' . $dep;
            }
        }

        if ($user->rating < $config['min_rating']->value) {
            $unmet[] = $config['min_rating']->name . ' rating required';
        } elseif ($user->rating === $config['min_rating']->value) {
                $ratingObtainedAt = $this->getRatingObtainedAt($user, $config['min_rating']);
            if (! $this->hasHeldRatingForMonths($ratingObtainedAt, $config['rating_months'])) {
                $unmet[] = sprintf(
                    '%s rating must be held for at least %d months',
                    $config['min_rating']->name,
                    $config['rating_months']
                );
            }
        }

        $logged = $hoursCache[$config['position']] ?? 0.0;
        if ($logged < $config['hours']) {
            $unmet[] = sprintf(
                'At least %dh on %s required (%.1fh logged)',
                $config['hours'],
                strtoupper($config['position']),
                $logged
            );
        }

        if (isset($config['extra_hours'])) {
            $prefix        = $config['extra_hours']['prefix'];
            $extraRequired = $config['extra_hours']['required'];
            $extraLogged   = $prefixHoursCache[$prefix] ?? 0.0;

            if ($extraLogged < $extraRequired) {
                $unmet[] = sprintf(
                    'At least %dh on %s positions required (%.1fh logged)',
                    $extraRequired,
                    rtrim($prefix, '_'),
                    $extraLogged
                );
            }
        }

        return [
            'eligible' => empty($unmet),
            'reason'   => empty($unmet) ? null : implode('; ', $unmet),
        ];
    }

    private function getRatingObtainedAt(User $user, VatsimRating $targetRating): ?Carbon
    {
        $training = $user->trainings
            ->filter(function ($training) use ($targetRating) {

                if ($training->status !== -1 || ! $training->closed_at) {
                    return false;
                }

                $rating = $training->getHighestVatsimRating();

                return $rating
                    && $rating->vatsim_rating === $targetRating->value;
            })
            ->sortByDesc('closed_at')
            ->first();

        return $training?->closed_at;
    }

    private function sumHoursByPositionType(array $sessions, array $suffixes): float
    {
        $total = 0.0;
        foreach ($sessions as $session) {
            $callsign = strtoupper($session['callsign'] ?? '');
            foreach ($suffixes as $suffix) {
                if (str_contains($callsign, $suffix)) {
                    $total += $this->sessionHours($session);
                    break;
                }
            }
        }
        return $total;
    }

    private function sumHoursByPrefix(array $sessions, string $prefix): float
    {
        $total  = 0.0;
        $prefix = strtoupper($prefix);
        foreach ($sessions as $session) {
            if (str_starts_with(strtoupper($session['callsign'] ?? ''), $prefix)) {
                $total += $this->sessionHours($session);
            }
        }
        return $total;
    }

    private function sessionHours(array $session): float
    {
        $logon  = $session['loggedOn']  ?? null;
        $logoff = $session['loggedOff'] ?? null;
        if ($logon === null || $logoff === null) return 0.0;

        return Carbon::parse($logon)->diffInSeconds(Carbon::parse($logoff)) / 3600;
    }

    private function hasHeldRatingForMonths(?Carbon $obtainedAt, int $months): bool
    {
        if ($obtainedAt === null) return false;
        return $obtainedAt->diffInMonths(Carbon::now()) >= $months;
    }
}