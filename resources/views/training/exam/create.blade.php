@extends('layouts.app')

@section('title', 'New Exam Report')
@section('content')


    {{-- 🔽 Trainee & Exam Metadata --}}
    <div class="col-xl-8 col-lg-12 col-md-12 mb-12">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-white">
                    {{ $training->user->name }}
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0" width="100%" cellspacing="0">
                        <thead class="table-light">
                        <tr>
                            <th>Vatsim ID</th>
                            <th>Current Rating</th>
                            <th>Training Rating</th>
                            @if(config('app.mode') == 'subdivision')
                                <th>Subdivision</th>
                            @else
                                <th>Division</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>{{ $training->user->id }}</td>
                            <td>{{ $training->user->rating_short }}</td>
                            <td>
                                @foreach($training->ratings as $rating)
                                    @if ($loop->last)
                                        {{ $rating->name }}
                                    @else
                                        {{ $rating->name . " + " }}
                                    @endif
                                @endforeach
                            </td>
                            <td>
                                @if(config('app.mode') == 'subdivision')
                                    {{ $training->user->subdivision }}
                                @else
                                    {{ $training->user->division }}
                                @endif
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div id="examFormWrapper">
        <form id="examForm">
        {{-- 🔽 Trainee & Exam Metadata --}}
        <div class="col-12 mb-4">
            <h5 class="fw-bold">Exam Information</h5>
            <div class="row">
                <div class="col-md-12 mb-3 d-flex gap-3 align-items-end">
                    <div class="flex-fill">
                        <label class="form-label" for="sessionDate">Date</label>
                        <input type="text" class="form-control datepicker" name="sessionDate" id="sessionDate" required>
                    </div>

                    <div class="flex-fill">
                        <label class="form-label" for="startTime">Start (Zulu)</label>
                        <input id="startTime" class="form-control" type="time" name="startTime" placeholder="12:00" required>
                    </div>

                    <div class="flex-fill">
                        <label class="form-label" for="endTime">End (Zulu)</label>
                        <input id="endTime" class="form-control" type="time" name="endTime" placeholder="12:00" required>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label" for="facilityTwr">Position</label>
                    <input
                        id="facilityTwr"
                        class="form-control @error('position') is-invalid @enderror"
                        type="text"
                        name="facilityTwr"
                        list="positions"
                        value="{{ old('position') }}"
                        required>

                    <datalist id="positions">
                        @foreach($positions as $position)
                            @browser('isFirefox')
                            <option>{{ $position->callsign }}</option>
                            @else
                                <option value="{{ $position->name }} - {{ $position->callsign }}">{{ $position->name }}</option>
                                @endbrowser
                                @endforeach
                    </datalist>

                    @error('position')
                    <span class="text-danger">{{ $errors->first('position') }}</span>
                    @enderror
                </div>
                    <div class="col-xl-12 col-xxl-6 mb-4">
                        <label class="form-label" for="examineeResult">Result</label>
                        <select class="form-select result-select" name="examineeResult" id="examineeResult" required onchange="updateResultColor(this)">
                            <option disabled selected>Choose a result</option>
                            <option value="FAILED">Failed</option>
                            <option value="PASSED">Passed</option>
                            <option value="INCOMPLETE">Incomplete</option>
                            <option value="POSTPONED">Postponed</option>
                        </select>
                    </div>
            </div>
        </div>


    {{-- 🔽 Examination Form --}}
    <div class="border-top mb-4"></div>

    <div class="row">
        <div class="col-12 mb-4">
            <h5 class="fw-bold">Examinee Information</h5>
            <div class="row">
                <div class="col-md-12 mb-3 d-flex gap-2 align-items-end">
                    <!-- Name -->
                    <div class="flex-fill">
                        <label class="form-label">Division Examiner</label>
                        <input type="text" class="form-control" name="nameExaminer" placeholder="Enter name" list="examiners">
                    </div>

                    <!-- ID -->
                    <div style="width: 100px;">
                        <label class="form-label">ID</label>
                        <input type="text" class="form-control" name="cidExaminer" placeholder="CID">
                    </div>

                    <!-- Rating -->
                    <div style="width: 100px;">
                        <label class="form-label">Rating</label>
                        <select class="form-select" name="ratingExaminer2">
                            <option value="" selected disabled>Select</option>
                            <option value="S2">S2</option>
                            <option value="S3">S3</option>
                            <option value="C1">C1</option>
                            <option value="C3">C3</option>
                            <option value="I1">I1</option>
                            <option value="I3">I3</option>
                            <option value="ADM">ADM</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Local Examiner(s)</label>
                    <div id="local-examiners-wrapper">
                        <input type="text" name="local_examiners[]" class="form-control mb-2"
                               placeholder="Enter examiner name" list="examiners">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addExaminer()">+ Add Examiner</button>
                </div>

                <datalist id="examiners">
                    @foreach($examiners as $ex)
                        @browser('isFirefox')
                        <option>{{ $ex->id }}</option>
                        @else
                            <option value="{{ $ex->name }}">{{ $ex->id }}</option>
                            @endbrowser
                            @endforeach
                </datalist>
            </div>
        </div>

        <div class="col-12 mb-4">
            <h5 class="fw-bold">Session Information</h5>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="sessionPerformed" class="form-label">Session Type</label>
                    <select name="sessionPerformed" id="sessionPerformed" class="form-select">
                        <option value="Online">Online</option>
                        <option value="Sweatbox">Sweatbox</option>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label for="complexity" class="form-label">Complexity</label>
                    <select name="complexity" id="complexity" class="form-select">
                        <option value="Low">Low</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label for="workload" class="form-label">Workload</label>
                    <select name="workload" id="workload" class="form-select">
                        <option value="Low">Low</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label for="trafficLoad" class="form-label">Traffic Load</label>
                    <select name="trafficLoad" id="trafficLoad" class="form-select">
                        <option value="Low">Low</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                    </select>
                </div>
            </div>
            </div>
        </div>

            {{-- 🔽 Competencies – General --}}
            <div class="col-12 mb-4">
                <h5 class="fw-bold">Competencies Assessment – General</h5>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                    <tr>
                        <th>Criteria</th>
                        <th style="width:150px;">Grade</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($examFields['GENERAL'] as $fieldName => $label)
                        @if($fieldName !== 'generalComments')
                            <tr>
                                <td>{{ $label }}</td>
                                <td>@include('partials.grade-select', ['name' => $fieldName])</td>
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
                <label class="form-label">Comments</label>
                <textarea class="form-control" name="generalComments"></textarea>
            </div>

            @if(!empty($examFields['ATC_COMPETENCIES']))
                <div class="col-12 mb-4">
                    <h5 class="fw-bold">ATC Competencies</h5>
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                        <tr><th>Criteria</th><th style="width:150px;">Grade</th></tr>
                        </thead>
                        <tbody>
                        @foreach($examFields['ATC_COMPETENCIES'] as $fieldName => $label)
                            @if($fieldName !== 'atcComments')
                                <tr>
                                    <td>{{ $label }}</td>
                                    <td>@include('partials.grade-select', ['name' => $fieldName])</td>
                                </tr>
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                    <label class="form-label">Comments</label>
                    <textarea class="form-control" name="atcComments"></textarea>
                </div>
            @endif

            @if(!empty($examFields['COMMUNICATIONS']))
                <div class="col-12 mb-4">
                    <h5 class="fw-bold">Communications</h5>
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                        <tr><th>Criteria</th><th style="width:150px;">Grade</th></tr>
                        </thead>
                        <tbody>
                        @foreach($examFields['COMMUNICATIONS'] as $fieldName => $label)
                            @if($fieldName !== 'commComments')
                                <tr>
                                    <td>{{ $label }}</td>
                                    <td>@include('partials.grade-select', ['name' => $fieldName])</td>
                                </tr>
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                    <label class="form-label">Comments</label>
                    <textarea class="form-control" name="commComments"></textarea>
                </div>
            @endif

        <div class="mb-3">
            <label for="finalReview" class="form-label">Final Review / Summary</label>
            <textarea name="finalReview" id="finalReview" class="form-control" rows="6"></textarea>
        </div>
            <button type="button" id="generatePdfBtn" class="btn btn-primary">Generate PDF</button>

    </form>
    </div>

        <!-- PDF Preview Wrapper -->
        <div id="pdfPreviewWrapper" style="display:none;">
            <!-- 🔽 Publish Form above PDF preview -->
            <div class="card-body">
                <form id="step2form" action="{{ route('training.examination.store', ['training' => $training->id]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <!-- Date & Position -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="examination_date" class="form-label">Date</label>
                            <input id="examination_date" class="datepicker form-control" type="text" name="examination_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="position" class="form-label">Position</label>
                            <input id="position" class="form-control" type="text" name="position" list="positions" required>
                            <datalist id="positions">
                                @foreach($positions as $position)
                                    <option value="{{ $position->callsign }}">{{ $position->name }}</option>
                                @endforeach
                            </datalist>
                        </div>
                    </div>

                    <!-- Result & Attachments -->
                    <div class="row">
                        <div class="col-xl-6 mb-4">
                            <label for="result" class="form-label">Result</label>
                            <select name="result" id="result" class="form-select" required>
                                <option disabled selected>Choose a result</option>
                                <option value="FAILED">Failed</option>
                                <option value="PASSED">Passed</option>
                                <option value="INCOMPLETE">Incomplete</option>
                                <option value="POSTPONED">Postponed</option>
                            </select>
                        </div>
                        <!--
                        <div class="col-xl-6 mb-4">
                            <label for="attachments" class="form-label">Attachments</label>
                            <input type="file" name="files[]" id="add-file" accept=".pdf" multiple>
                        </div>
                        -->
                    </div>

                    <!-- Rating upgrade (conditional if PASSED) -->
                    <div id="upgradeSection" style="display:none;">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="user" class="form-label">Request rating upgrade from</label>
                                <input id="user" class="form-control" type="text" name="request_task_user_id" list="userList">
                                <datalist id="userList">
                                    @foreach($taskRecipients as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="col-md-6">
                                @if($training->ratings->whereNotNull('vatsim_rating')->count() > 1)
                                    <label for="chooseRating" class="form-label">Upgrade to rating</label>
                                    <select name="subject_training_rating_id" class="form-select">
                                        @foreach($training->ratings->whereNotNull('vatsim_rating')->sortByDesc('id') as $rating)
                                            <option value="{{ $rating->id }}">{{ $rating->name }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="button" id="editBtn" class="btn btn-secondary mt-3">Edit</button>
                    </div>

                    <button type="submit" id="publishBtn" class="btn btn-success mt-3">Publish Examination Report</button>

                </form>
            </div>

            <!-- 🔽 PDF Preview iframe -->
            <iframe id="pdfPreview" style="width:100%; height:600px; border:1px solid #ccc;"></iframe>
        </div>





        @endsection

@section('js')

@vite('resources/js/vue.js')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const application = createApp({
            data(){
                return {
                    result: null,
                }
            }
        }).mount('#examreport')
    })
</script>

<!-- Flatpickr -->
@vite(['resources/js/flatpickr.js', 'resources/sass/flatpickr.scss'])
            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    let pdfBlob = null; // store PDF in memory


                    // Initialize Flatpickr
                    var defaultDate = "{{ old('date') }}";
                    document.querySelectorAll('.datepicker').forEach(el => {
                        flatpickr(el, {
                            disableMobile: true,
                            minDate: "{!! date('Y-m-d', strtotime('-1 months')) !!}",
                            maxDate: "{!! date('Y-m-d') !!}",
                            dateFormat: "d/m/Y",
                            defaultDate: defaultDate,
                            locale: {firstDayOfWeek: 1}
                        });
                    });

                    // Examiners dictionary
                    const examiners = {
                        @foreach($examiners as $ex)
                        "{{ $ex->name }}": {id: "{{ $ex->id }}", rating: "{{ $ex->rating_short }}"},
                        @endforeach
                    };

                    document.getElementById('generatePdfBtn').addEventListener('click', async () => {
                        const form = document.getElementById('examForm');
                        const formData = new FormData(form);
                        const data = {};

                        formData.forEach((value, key) => {
                            if (key.endsWith("[]")) {
                                const cleanKey = key.slice(0, -2);
                                if (!data[cleanKey]) data[cleanKey] = [];
                                data[cleanKey].push(value);
                            } else {
                                data[key] = value;
                            }
                        });

                        data.local_examiners = (data.local_examiners || []).map((name) => {
                            const ex = examiners[name] || {};
                            return {name, id: ex.id || "", rating: ex.rating || ""};
                        });

                        data.examineeCid = "{{$training->user->id}}";
                        data.examineeName = "{{$training->user->name}}";
                        data.rating = "{{$lastRating}}";

                        // Request PDF
                        const response = await fetch('https://www.vatita.net/pdfapi/controlcenter', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify(data)
                        });

                        pdfBlob = await response.blob();
                        const url = URL.createObjectURL(pdfBlob);

                        // Prefill step2form
                        const step2form = document.getElementById('step2form');
                        step2form.querySelector('#examination_date').value = data.sessionDate;

                        const posParts = data.facilityTwr.split('-');
                        step2form.querySelector('#position').value = posParts.length > 1 ? posParts[1].trim() : data.facilityTwr;

                        step2form.querySelector('#result').value = data.examineeResult;

                        const upgradeSection = document.getElementById('upgradeSection');
                        upgradeSection.style.display = (data.examineeResult === "PASSED") ? 'block' : 'none';

                        // Attach PDF as hidden file input immediately
                        let oldInput = step2form.querySelector('input[name="files[]"]');
                        if (oldInput) oldInput.remove();

                        const dt = new DataTransfer();
                        dt.items.add(new File([pdfBlob], "exam-report.pdf", { type: "application/pdf" }));

                        const fileInput = document.createElement('input');
                        fileInput.type = 'file';
                        fileInput.name = 'files[]';
                        fileInput.files = dt.files;
                        fileInput.hidden = true;
                        step2form.appendChild(fileInput);

                        // Show PDF preview
                        document.getElementById('examFormWrapper').style.display = 'none';
                        const preview = document.getElementById('pdfPreview');
                        preview.src = url;
                        document.getElementById('pdfPreviewWrapper').style.display = 'block';
                    });

                    // === Edit button ===
                    document.getElementById('editBtn').addEventListener('click', () => {
                        document.getElementById('pdfPreviewWrapper').style.display = 'none';
                        document.getElementById('examFormWrapper').style.display = 'block';
                    });

                    // === Publish button ===
                    document.getElementById('publishBtn').addEventListener('click', (e) => {
                        if (!pdfBlob) {
                            e.preventDefault();
                            alert("Generate the PDF first before publishing!");
                            return;
                        }

                        const step2form = document.getElementById('step2form');
                        const dt = new DataTransfer();
                        dt.items.add(new File([pdfBlob], "exam-report.pdf", {type: "application/pdf"}));

                        let fileInput = step2form.querySelector('input[name="files[]"]');
                        if (!fileInput) {
                            fileInput = document.createElement('input');
                            fileInput.type = 'file';
                            fileInput.name = 'files[]';
                            fileInput.hidden = true;
                            step2form.appendChild(fileInput);
                        }
                        fileInput.files = dt.files;

                        step2form.submit();
                    });
                });

                    // === Color grading and results ===
                function updateGradeColor(select) {
                    select.classList.remove("grade-i", "grade-s", "grade-g");
                    if (select.value === "I") select.classList.add("grade-i");
                    else if (select.value === "S") select.classList.add("grade-s");
                    else if (select.value === "G") select.classList.add("grade-g");
                }

                function updateResultColor(select) {
                    select.classList.remove("grade-g", "grade-i", "result-incomplete");
                    switch (select.value) {
                        case "PASSED": select.classList.add("grade-g"); break;
                        case "FAILED": select.classList.add("grade-i"); break;
                        case "INCOMPLETE":
                        case "POSTPONED": select.classList.add("result-incomplete"); break;
                    }
                }
                function addExaminer() {
                    const wrapper = document.getElementById('local-examiners-wrapper');
                    const input = document.createElement('input');
                    if (input > 2) {
                        alert("Max 2 instructors");
                        return;
                    }
                    input.type = 'text';
                    input.name = 'local_examiners[]';
                    input.className = 'form-control mb-2';
                    input.placeholder = 'Enter examiner';
                    input.setAttribute('list', 'examiners');
                    wrapper.appendChild(input);
                }
                </script>
@endsection
