@extends('layouts.app')

@section('title', 'View Training Report')
@section('content')

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 fw-bold text-white">
                        Training Report for {{ $training->user->first_name }}'s training for
                        @foreach($training->ratings as $rating)
                            @if ($loop->last)
                                {{ $rating->name }}
                            @else
                                {{ $rating->name . " + " }}
                            @endif
                        @endforeach
                    </h6>
                </div>
                <div class="card-body">

                    <div class="mb-3">
                        <label class="form-label" for="position">Position</label>
                        <input
                            id="position"
                            class="form-control"
                            type="text"
                            value="{{ $evaluation->position }}"
                            disabled>
                    </div>

                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-12 mb-3 d-flex gap-3 align-items-end">
                                <div class="flex-fill">
                                    <label class="form-label" for="date">Date</label>
                                    <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($evaluation->date)->format('d/m/Y') }}" disabled>
                                </div>

                                <div class="flex-fill">
                                    <label class="form-label" for="startTime">Start (Zulu)</label>
                                    <input class="form-control" type="text" value="{{ $evaluation->start }}" disabled>
                                </div>

                                <div class="flex-fill">
                                    <label class="form-label" for="endTime">End (Zulu)</label>
                                    <input class="form-control" type="text" value="{{ $evaluation->end }}" disabled>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h5 class="fw-bold">Session Information</h5>
                        <div class="row">
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Session Type</label>
                                <input class="form-control" value="{{ $evaluation->sessionPerformed }}" disabled>
                            </div>

                            <div class="col-md-2 mb-3">
                                <label class="form-label">Complexity</label>
                                <input class="form-control" value="{{ $evaluation->complexity }}" disabled>
                            </div>

                            <div class="col-md-2 mb-3">
                                <label class="form-label">Workload</label>
                                <input class="form-control" value="{{ $evaluation->workload }}" disabled>
                            </div>

                            <div class="col-md-2 mb-3">
                                <label class="form-label">Traffic Load</label>
                                <input class="form-control" value="{{ $evaluation->trafficLoad }}" disabled>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Training Phase</label>
                                <input class="form-control" value="{{ $evaluation->trainingPhase }}" disabled>
                            </div>
                        </div>
                    </div>

                    @foreach($itemsByCategory as $category => $categoryData)
                        <h5 class="mt-4">{{ $categoryData['humanName'] }}</h5>
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th style="width: 60%;">Description</th>
                                <th style="width: 8%;">Vote</th>
                                <th style="width: 32%;">Comment</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($categoryData['items'] as $i => $item)
                                <tr>
                                    <td>{{ $item->description }}</td>
                                    <td class="text-center">
                                        <input class="form-control text-center"
                                               value="{{ $results[$item->item_id]->vote ?? '' }}"
                                               disabled
                                               style="background-color:
                                                @if(($results[$item->item_id]->vote ?? '') == 'I') #dc3545
                                                @elseif(($results[$item->item_id]->vote ?? '') == 'S') #90ee90
                                                @elseif(($results[$item->item_id]->vote ?? '') == 'G') #198754
                                                @endif;
                                                color:
                                                @if(($results[$item->item_id]->vote ?? '') == 'I' || ($results[$item->item_id]->vote ?? '') == 'G') #fff
                                                @else #000
                                                @endif;
                                                font-weight: bold;">
                                    </td>
                                    <td>
                                        <textarea class="form-control" rows="1" disabled>{{ $results[$item->item_id]->comment ?? '' }}</textarea>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endforeach

                    <hr>

                    <div class="mb-3">
                        <h5><label class="form-label">Final Review</label></h5>
                        @markdown($evaluation->finalReview ?? '')
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection
