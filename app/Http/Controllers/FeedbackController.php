<?php

namespace App\Http\Controllers;

use anlutro\LaravelSettings\Facade as Setting;
use App\Models\Feedback;
use App\Models\Position;
use App\Models\User;
use App\Notifications\FeedbackNotification;
use App\Notifications\FeedbackNotificationUser;
use App\Notifications\PositiveFeedbackNotification;
use App\Services\DiscordNotifier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): View|RedirectResponse
    {

        if (! Setting::get('feedbackEnabled')) {
            return redirect()->route('dashboard')->withErrors('Feedback is currently disabled.');
        }

        $positions = Position::all();
        $controllers = User::getAssociatedActiveAtcMembers()->sortBy('name')->values();

        return view('feedback.create', compact('positions', 'controllers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): RedirectResponse
    {

        if (! Setting::get('feedbackEnabled')) {
            return redirect()->route('dashboard')->withErrors('Feedback is currently disabled.');
        }

        $data = $request->validate([
            'position' => 'nullable|exists:positions,callsign',
            'controller' => 'nullable|integer|exists:users,id',
            'feedback' => 'required',
            'visibilityToggle' => 'nullable',
            'emailToggle' => 'nullable',
        ]);

        $position = isset($data['position']) ? Position::where('callsign', $data['position'])->get()->first() : null;
        $controller = isset($data['controller']) ? User::find($data['controller']) : null;
        $feedback = $data['feedback'];
        $visible = false;
        $followup = false;

        if (isset($data['visibilityToggle']) && $data['visibilityToggle'] == 'on') {
            $visible = true;
        }
        if (isset($data['emailToggle']) && $data['emailToggle'] == 'on') {
            $followup = true;
        }

        $submitter = auth()->user();

        $feedback = Feedback::create([
            'feedback' => $feedback,
            'submitter_user_id' => $submitter->id,
            'reference_user_id' => isset($controller) ? $controller->id : null,
            'reference_position_id' => isset($position) ? $position->id : null,
            'visibility' => $visible,
            'followup' => $followup,
        ]);

        if ($controller && $visible) {
            $controller->notify(new FeedbackNotificationUser($controller, $feedback));
        }

        // Forward email if configured
        if (Setting::get('feedbackForwardEmail')) {
            $feedback->notify(new FeedbackNotification($feedback));
        }

        $toContr = isset($controller) ? $controller->id : 'N/A';

        DiscordNotifier::send(
            'Feedback received',
            "Controller: {$toContr}",
            'info',
            [
                'Message' => $feedback->feedback,
            ]
        );

        return redirect()->route('dashboard')->with('success', 'Feedback submitted!');

    }

    public function reply(Request $request): RedirectResponse
    {
        if (! Setting::get('feedbackEnabled')) {
            return redirect()->route('dashboard')->withErrors('Feedback is currently disabled.');
        }

        $this->authorize('viewFeedback', \App\Models\ManagementReport::class);

        $data = $request->validate([
            'feedback' => 'required|exists:feedback,id',
        ]);

        $sender = auth()->user();
        $feedback = Feedback::find($data['feedback']);

        if (! $feedback) {
            return redirect()->back()->withErrors('Feedback not found.');
        }

        if( $feedback->reply_sent ) {
            return redirect()->back()->withErrors('Reply has already been sent for this entry.');
        }

        $feedback->notify(new PositiveFeedbackNotification($sender, $feedback));

        $feedback->reply_sent = true;
        $feedback->replied_at = now();
        $feedback->save();

        return redirect()->back()->with('success', 'Sent and marked.');

    }

    /**
     * Return rendered positive feedback HTML fragment for modal preview.
     */
    public function previewFragment(\App\Models\Feedback $feedback): JsonResponse
    {
        $this->authorize('viewFeedback', \App\Models\ManagementReport::class);

        $recipient = $feedback->submitter;
        $sender = auth()->user();

        $html = app(\Illuminate\Mail\Markdown::class)->render(
            'mail.positive_feedback',
            [
                'firstName' => $recipient->first_name,
                'sender' => $sender->first_name,
            ]
        )->toHtml();

        return response()->json(['html' => $html]);
    }
}
