<?php

namespace App\Http\Controllers;

use anlutro\LaravelSettings\Facade as Setting;
use App\Models\Feedback;
use App\Models\Position;
use App\Models\User;
use App\Notifications\FeedbackNotification;
use App\Notifications\FeedbackNotificationUser;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        if (! Setting::get('feedbackEnabled')) {
            return redirect()->route('dashboard')->withErrors('Feedback is currently disabled.');
        }

        $positions = Position::all();
        $controllers = User::all();

        return view('feedback.create', compact('positions', 'controllers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        if (! Setting::get('feedbackEnabled')) {
            return redirect()->route('dashboard')->withErrors('Feedback is currently disabled.');
        }

        $data = $request->validate([
            'position' => 'nullable|exists:positions,callsign',
            'controller' => 'nullable|exists:users,id',
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


        if($controller && $visible) {
            $controller->notify(new FeedbackNotificationUser($controller, $feedback));
        }

        // Forward email if configured
        if (Setting::get('feedbackForwardEmail')) {
            $feedback->notify(new FeedbackNotification($feedback));
        }

        return redirect()->route('dashboard')->with('success', 'Feedback submitted!');

    }
}
