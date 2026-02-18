<?php

namespace App\Policies;

use App\Models\Evaluation;
use App\Models\OneTimeLink;
use App\Models\Training;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class EvaluationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any of the reports related to a training
     *
     * @return bool
     */
    public function viewAny(User $user, Training $training)
    {
        return $training->mentors->contains($user) ||
            $user->is($training->user) ||
            $user->isModeratorOrAbove($training->area) ||
            $user->isAdmin();
    }

    /**
     * Determine whether the user can view the training report.
     *
     * @return bool
     */
    public function view(User $user, Evaluation $ev)
    {
        $isTrainee = $user->is($ev->training->user);

        return
            (
                // Mentors can see all, but not drafts of their own training
                $ev->training->mentors->contains($user)
                && !($isTrainee && $ev->draft)
            ) ||
            $ev->training->mentors->contains($user) || // If the user is a mentor of the training
            $user->id === $ev->examiner_id ||               // If the user is the author of the evaluation
            $user->isAdmin() ||
            $user->isModerator($ev->training->area) ||
            ($user->id === $ev->training->user_id && ! $ev->draft);
    }

    /**
     * Determine whether the user can create training reports.
     *
     * @return bool
     */
    public function create(User $user, Training $training)
    {
        if (($link = $this->getOneTimeLink($training)) != null) {
            return $user->isModerator($link->training->area) || $user->isMentor($link->training->area);
        }

        // Check if mentor is mentoring area, not filling their own training and the training is in progress
        return $user->isModerator($training->area) || ($training->mentors->contains($user) && $user->isNot($training->user));
    }

    /**
     * Determine whether the user can update the training report.
     *
     * @return bool
     */
    public function update(User $user, Evaluation $ev)
    {
        return $ev->training->mentors->contains($user) ||
            $user->isAdmin() ||
            $user->isModerator($ev->training->area);
    }

    /**
     * Determine whether the user can delete the training report.
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function delete(User $user, Evaluation $ev)
    {
        /**
         * allow only if:
         * - user is admin
         * otherwise
         * - if it's the creator of that trainingreport, only after 10 minutes of its creation
         */
        $isAdmin = $user->isAdmin();

        $isMentorAllowed = $user->id === $ev->examiner_id && $ev->created_at->gt(now()->subMinutes(5));


        if($isAdmin || $isMentorAllowed) {
            return Response::allow();
        } else {
            return Response::deny('Only moderators and the author of the training report can delete it.');
        }
    }

    /**
     * Get the one time link from a session given a training
     *
     * @return null
     */
    private function getOneTimeLink($training)
    {
        $link = null;

        $key = session()->get('onetimekey');

        if ($key != null) {
            $link = OneTimeLink::where([
                ['training_id', '=', $training->id],
                ['key', '=', $key],
            ])->get()->first();
        }

        return $link;
    }
}
