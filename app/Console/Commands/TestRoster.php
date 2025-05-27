<?php

namespace App\Console\Commands;

use anlutro\LaravelSettings\Facade as Setting;
use App\Facades\DivisionApi;
use App\Models\User;
use Illuminate\Console\Command;

class TestRoster extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:roster';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dry runs new roster sync';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!Setting::get('divisionApiEnabled')) {
            $this->error('This command is only available when Division API setting is enabled');
            return Command::FAILURE;
        }

        $this->info('Fetching roster with Division API...');

        $rosterResponse = DivisionApi::getRoster();
        if ($rosterResponse && $rosterResponse->successful()) {
            $json = $rosterResponse->json();
            if (isset($json['data']) && isset($json['data']['roster_members'])) {
                $rosteredMembers = collect($json['data']['roster_members'])->pluck('user_cid');

                // 1. Get active ATC members in ITA subdivision
                $itaMembers = User::getActiveAtcMembers()
                    ->where('subdivision', 'ITA')
                    ->pluck('id');

                // 2. Get all visiting controllers (regardless of subdivision)
                $visitingMembers = User::whereHas('endorsements', function ($query) {
                    $query->where('type', 'VISITING')
                        ->where('revoked', false)
                        ->where('expired', false);
                })->pluck('id');

                // Combine both groups
                $shouldBeOnRoster = $itaMembers->merge($visitingMembers)->unique();

                $this->info('Members that should be on roster: ' . $shouldBeOnRoster->implode(', '));
                $this->info('Current roster members: ' . $rosteredMembers->implode(', '));

                // Calculate differences
                $newMembers = $shouldBeOnRoster->diff($rosteredMembers);
                $removedMembers = $rosteredMembers->diff($shouldBeOnRoster);

                $this->info("\nSummary:");
                $this->info("-------");
                $this->info("Total members in ITA subdivision: " . $itaMembers->count());
                $this->info("Total visiting controllers: " . $visitingMembers->count());
                $this->info("Total members that should be rostered: " . $shouldBeOnRoster->count());
                $this->info("Current roster count: " . $rosteredMembers->count());
                $this->info("Members to add: " . $newMembers->count());
                $this->info("Members to remove: " . $removedMembers->count());

                // Dry-run mode (commented out actual modifications)
                $this->info("\nWould add these members:");
                $newMembers->each(function ($memberId) {
                    $user = User::find($memberId);
                    if(!$user) {
                        return $this->info("- $memberId | N/A ");
                    }
                    $name = $user->first_name . ' ' . $user->last_name;
                    $this->info("- $memberId | $name");
                    // $response = DivisionApi::assignRosterUser($memberId);
                    // Handle response...
                });

                $this->info("\nWould remove these members:");
                $removedMembers->each(function ($memberId) {
                    $user = User::find($memberId);
                    if(!$user) {
                        return $this->info("- $memberId | N/A ");
                    }
                    $name = $user->first_name . ' ' . $user->last_name;
                    $this->info("- $memberId | $name");
                    // $response = DivisionApi::removeRosterUser($memberId);
                    // Handle response...
                });

                $this->info("\nSync completed (dry run mode)");
            }
        } else {
            $this->error('Failed to fetch roster: ' . ($rosterResponse->json()['message'] ?? 'Unknown error'));
        }
    }
}
