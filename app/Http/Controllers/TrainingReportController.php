<?php

namespace App\Http\Controllers;

use App\Helpers\TrainingStatus;
use App\Models\Evaluation;
use App\Models\EvaluationItem;
use App\Models\EvaluationResult;
use App\Models\OneTimeLink;
use App\Models\Position;
use App\Models\Training;
use App\Models\TrainingReport;
use App\Notifications\TrainingReportNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller for handling training reports
 */
class TrainingReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Training $training)
    {
        $this->authorize('viewAny', [TrainingReport::class, $training]);

        $reports = Auth::user()->viewableModels(TrainingReport::class, [['training_id', '=', $training->id]]);

        return view('training.report.index', compact('training', 'reports'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create(Request $request, Training $training)
    {
        $this->authorize('create', [TrainingReport::class, $training]);
        if ($training->status < TrainingStatus::PRE_TRAINING->value) {
            return redirect(null, 400)->back()->withErrors('Training report cannot be created for a training not in progress.');
        }

        $lastRating = $training->ratings->last()->name;
        $ratingMap = [
            'S1' => 2,
            'S2' => 3,
            'S3' => 4,
            'C1' => 5,
        ];

        $ratingNumber = $ratingMap[$lastRating] ?? null;

        $positions = Position::where('rating', '=', $ratingNumber)->get();
        $evaluationItems = EvaluationItem::where('rating', $lastRating)->get();
        $itemsByCategory = $evaluationItems->groupBy('category');

        // Keep the onetimekey for another request
        $request->session()->reflash();

        return view('training.report.create', compact('training', 'positions', 'evaluationItems', 'itemsByCategory'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function store(Request $request, Training $training)
    {
        $this->authorize('create', [TrainingReport::class, $training]);

        $data = $this->validateRequest();
        $data['written_by_id'] = Auth::id();
        $data['training_id'] = $training->id;

        $date = Carbon::createFromFormat('d/m/Y', $data['report_date'])->format('Y-m-d');

        $evaluation = Evaluation::create([
            'student_id' => $training->user->id,
            'level' => $training->ratings->last()->name,
            'date' => $date,
            'position' => $data['position'],
            'examiner_id' => Auth::id(),
            'training_id' => $training->id,
        ]);

        if (!empty($data['results']) && is_array($data['results'])) {
            foreach ($data['results'] as $key => $item) {
                $itemId = $item['id'] ?? $key;

                EvaluationResult::create([
                    'eval_id' => $evaluation->eval_id,
                    'item_id' => $itemId,
                    'vote' => $item['vote'] ?? '',
                    'comment' => $item['comment'] ?? '',
                ]);
            }
        }

        if (isset($data['report_date'])) {
            $data['report_date'] = Carbon::createFromFormat('d/m/Y', $data['report_date'])->format('Y-m-d H:i:s');
        }

        // Remove attachments , they are added in next step
        unset($data['files']);
        unset($data['results']); // remove it before creating the TrainingReport
        $data['content'] = "";
        $data['contentimprove'] = "";

        /*$report = TrainingReport::create($data);

        // Add attachments
        TrainingObjectAttachmentController::saveAttachments($request, $report);

        // Notify student of new training request if it's not a draft
        if ($report->draft != true && $training->user->setting_notify_newreport) {
        //    $training->user->notify(new TrainingReportNotification($training, $report));
        }
        */
        if (($key = session()->get('onetimekey')) != null) {
            // Remove the link
            OneTimeLink::where('key', $key)->delete();
            session()->pull('onetimekey');

            return redirect(route('user.reports', Auth::user()))->withSuccess('Report successfully created');
        }

        return redirect(route('training.show', $training->id))->withSuccess('Report successfully created');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(TrainingReport $trainingReport)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Evaluation $evaluation)
    {
        $this->authorize('update', $evaluation);

        $training = $evaluation->training;
        $lastRating = $training->ratings->last()->name;
        $ratingMap = [
            'S1' => 2,
            'S2' => 3,
            'S3' => 4,
            'C1' => 5,
        ];
        $ratingNumber = $ratingMap[$lastRating] ?? null;

        $positions = Position::where('rating', $ratingNumber)->get();
        $evaluationItems = EvaluationItem::where('rating', $lastRating)->get();
        $itemsByCategory = $evaluationItems->groupBy('category');

        $results = $evaluation->results()->with('item')->get()->keyBy('item_id');

        return view('training.report.edit', compact('evaluation', 'positions', 'training', 'itemsByCategory', 'results'));
    }



    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, Evaluation $evaluation)
    {
        $this->authorize('update', $evaluation);

        $data = $this->validateRequest();

        // Update evaluation date if provided
        if (isset($data['report_date'])) {
            $evaluation->date = Carbon::createFromFormat('d/m/Y', $data['report_date'])->format('Y-m-d');
        }

        // Update other fields
        $evaluation->position = $data['position'] ?? $evaluation->position;

        $evaluation->save();

        // Update individual results
        if (!empty($data['results']) && is_array($data['results'])) {
            foreach ($data['results'] as $itemId => $resultData) {
                $result = $evaluation->results()->where('item_id', $itemId)->first();
                if ($result) {
                    $result->vote = $resultData['vote'] ?? $result->vote;
                    $result->comment = $resultData['comment'] ?? $result->comment;
                    $result->save();
                }
            }
        }

        return redirect()->intended(route('training.show', $evaluation->training_id))
            ->withSuccess('Evaluation successfully updated');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(TrainingReport $report)
    {
        $this->authorize('delete', $report);

        $report->delete();

        return redirect(route('training.show', $report->training->id))->withSuccess('Training report deleted');
    }

    /**
     * Validates the request data
     *
     * @return mixed
     */
    protected function validateRequest()
    {
        return request()->validate([
            'report_date' => 'required|date_format:d/m/Y',
            'position' => 'nullable',
            'results' => 'required|array',
            'results.*.vote' => 'nullable|in:I,S,G',
            'results.*.comment' => 'nullable|string|max:255',
        ]);
    }
}
