@extends('layouts.app')

@section('title', 'New Training Report')
@section('content')

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 fw-bold text-white">
                        New Training Report for {{ $training->user->first_name }}'s training for
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
                    <form action="{{ route('training.report.store', ['training' => $training->id]) }}" method="POST"
                          enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label" for="position">Position</label>
                            <input
                                id="position"
                                class="form-control @error('position') is-invalid @enderror"
                                type="text"
                                name="position"
                                list="positions"
                                value="{{ old('position') }}"
                                required>

                            <datalist id="positions">
                                @foreach($positions as $position)
                                    @browser('isFirefox')
                                    <option>{{ $position->callsign }}</option>
                                    @else
                                        <option value="{{ $position->callsign }}">{{ $position->name }}</option>
                                        @endbrowser
                                        @endforeach
                            </datalist>

                            @error('position')
                            <span class="text-danger">{{ $errors->first('position') }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="row">
                                <div class="col-md-12 mb-3 d-flex gap-3 align-items-end">
                                    <div class="flex-fill">
                                        <label class="form-label" for="date">Date</label>
                                        <input type="text"
                                               class="form-control datepicker  @error('report_date') is-invalid @enderror"
                                               name="report_date" id="date" value="{{ old('report_date') }}" required>
                                    </div>

                                    <div class="flex-fill">
                                        <label class="form-label" for="startTime">Start (Zulu)</label>
                                        <input id="startTime" class="form-control" type="time" name="startTime"
                                               placeholder="12:00" required>
                                    </div>

                                    <div class="flex-fill">
                                        <label class="form-label" for="endTime">End (Zulu)</label>
                                        <input id="endTime" class="form-control" type="time" name="endTime"
                                               placeholder="12:00" required>
                                    </div>
                                </div>
                            </div>
                            @error('report_date')
                            <span class="text-danger">{{ $errors->first('report_date') }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <h5 class="fw-bold">Session Information</h5>
                            <div class="row">
                                <div class="col-md-2 mb-3">
                                    <label for="sessionPerformed" class="form-label">Session Type</label>
                                    <select name="sessionPerformed" id="sessionPerformed" class="form-select">
                                        <option value="Online">Online</option>
                                        <option value="Sweatbox">Sweatbox</option>
                                    </select>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label for="complexity" class="form-label">Complexity</label>
                                    <select name="complexity" id="complexity" class="form-select">
                                        <option value="Low">Low</option>
                                        <option value="Medium">Medium</option>
                                        <option value="High">High</option>
                                    </select>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label for="workload" class="form-label">Workload</label>
                                    <select name="workload" id="workload" class="form-select">
                                        <option value="Low">Low</option>
                                        <option value="Medium">Medium</option>
                                        <option value="High">High</option>
                                    </select>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label for="trafficLoad" class="form-label">Traffic Load</label>
                                    <select name="trafficLoad" id="trafficLoad" class="form-select">
                                        <option value="Low">Low</option>
                                        <option value="Medium">Medium</option>
                                        <option value="High">High</option>
                                    </select>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="trainingPhase" class="form-label">Training Phase</label>
                                    <select name="trainingPhase" id="trainingPhase" class="form-select">
                                        <option value="Basic">Basic</option>
                                        <option value="PreIntermediate">Pre-intermediate</option>
                                        <option value="Intermediate">Intermediate</option>
                                        <option value="Advanced">Advanced</option>
                                        <option value="ExamType">Exam Type</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        @foreach($itemsByCategory as $category => $categoryData)
                            <h5 class="mt-4 fw-bold">{{ $categoryData['humanName'] }}</h5>
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th style="width: 60%;">Description</th>
                                    <th style="width: 8%;">Vote</th>
                                    <th style="width: 32%;">Comment</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($categoryData['items'] as $item)
                                    <tr>
                                        <td>{{ $item->description }}</td>
                                        <td class="text-center">
                                            <select class="form-select form-select-sm vote-select"
                                                    name="results[{{ $item->item_id }}][vote]"
                                            >
                                                <option value="">Select</option>
                                                <option
                                                    value="I" {{ (old('results.'.$item->item_id.'.vote') ?? $results[$item->item_id]->vote ?? '') == 'I' ? 'selected' : '' }}>
                                                    I
                                                </option>
                                                <option
                                                    value="S" {{ (old('results.'.$item->item_id.'.vote') ?? $results[$item->item_id]->vote ?? '') == 'S' ? 'selected' : '' }}>
                                                    S
                                                </option>
                                                <option
                                                    value="G" {{ (old('results.'.$item->item_id.'.vote') ?? $results[$item->item_id]->vote ?? '') == 'G' ? 'selected' : '' }}>
                                                    G
                                                </option>
                                            </select>
                                        </td>
                                        <td>
                                         <textarea class="form-control comment-textarea"
                                                   name="results[{{ $item->item_id }}][comment]"
                                                   maxlength="255"
                                                   rows="1"
                                                   placeholder="Enter comment...">{{ old('results.'.$item->item_id.'.comment') ?? $results[$item->item_id]->comment ?? '' }}</textarea>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endforeach
                        <hr>

                        <div class="mb-3">
                            <label class="form-label" for="finalReview">Final Review</label>
                            <textarea class="form-control @error('finalReview') is-invalid @enderror" name="finalReview"
                                      id="finalReview" rows="4"
                                      placeholder="In which areas do the student need to improve?">{{ old('contentimprove') }}</textarea>
                            @error('finalReview')
                            <span class="text-danger">{{ $errors->first('finalReview') }}</span>
                            @enderror
                        </div>

                        {{--
                        @if(session()->get('onetimekey') == null)
                            <div class="mb-3 form-check">
                                <input type="checkbox" value="1" class="form-check-input @error('draft') is-invalid @enderror" name="draft" id="draftCheck">
                                <label class="form-check-label" name="draft" for="draftCheck">Save as draft</label>
                                @error('draft')
                                    <span class="text-danger">{{ $errors->first('draft') }}</span>
                                @enderror
                            </div>
                        @endif
                        --}}


                        <button type="submit" id="training-submit-btn" class="btn btn-success">Save report</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')

    <!-- Flatpickr -->
    @vite(['resources/js/flatpickr.js', 'resources/sass/flatpickr.scss'])
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var defaultDate = "{{ old('report_date') }}"
            document.querySelector('.datepicker').flatpickr({
                disableMobile: true,
                minDate: "{!! date('Y-m-d', strtotime('-1 months')) !!}",
                maxDate: "{!! date('Y-m-d') !!}",
                dateFormat: "d/m/Y",
                defaultDate: defaultDate,
                locale: {firstDayOfWeek: 1}
            });
        });
    </script>

    <!-- Markdown Editor -->
    @vite(['resources/js/easymde.js', 'resources/sass/easymde.scss'])
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var simplemde1 = new EasyMDE({
                element: document.getElementById("contentBox"),
                status: false,
                toolbar: ["bold", "italic", "heading-3", "|", "quote", "unordered-list", "ordered-list", "|", "link", "preview", "side-by-side", "fullscreen", "|", "guide"],
                insertTexts: {
                    link: ["[", "](link)"],
                }
            });
            var simplemde2 = new EasyMDE({
                element: document.getElementById("finalReview"),
                status: false,
                toolbar: ["bold", "italic", "heading-3", "|", "quote", "unordered-list", "ordered-list", "|", "link", "preview", "side-by-side", "fullscreen", "|", "guide"],
                insertTexts: {
                    link: ["[", "](link)"],
                }
            });

            var submitClicked = false
            document.addEventListener("submit", function (event) {
                if (event.target.tagName === "FORM") {
                    submitClicked = true;
                }
            });

// Confirm closing window if there are unsaved changes
            window.addEventListener('beforeunload', function (e) {
                if (!submitClicked && (simplemde1.value() != '' || simplemde2.value() != '')) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        })


        document.addEventListener('DOMContentLoaded', function () {
            const voteSelects = document.querySelectorAll('.vote-select');

            function updateColor(select) {
                const val = select.value;
                if (val === 'I') {
                    select.style.backgroundColor = '#dc3545'; // red
                    select.style.color = '#fff';
                    select.style.fontWeight = 'bold';
                } else if (val === 'S') {
                    select.style.backgroundColor = '#90ee90'; // yellow
                    select.style.color = '#000';
                    select.style.fontWeight = 'bold';
                } else if (val === 'G') {
                    select.style.backgroundColor = '#198754'; // green
                    select.style.color = '#fff';
                    select.style.fontWeight = 'bold';
                } else {
                    select.style.backgroundColor = '';
                    select.style.color = '';
                }
            }

            voteSelects.forEach(select => {
                updateColor(select); // initial color
                select.addEventListener('change', () => updateColor(select));
            });
        });

    </script>

@endsection
