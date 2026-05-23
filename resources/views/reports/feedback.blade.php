@extends('layouts.app')

@section('title', 'Feedback')

@section('header')
    @vite(['resources/sass/bootstrap-table.scss', 'resources/js/bootstrap-table.js'])
@endsection

@section('content')

<div class="row">
    <div class="col-xl-12 col-md-12 mb-12">

        <div class="card shadow mb-4">
            <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-white">
                    Feedback
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-leftpadded mb-0" width="100%" cellspacing="0"
                        data-cookie="true"
                        data-cookie-id-table="mentors"
                        data-cookie-expire="90d"
                        data-page-size="25"
                        data-toggle="table"
                        data-pagination="true"
                        data-filter-control="true"
                        data-sort-reset="true">
                        <thead class="table-light">
                            <tr>
                                <th data-field="received" data-sortable="true">Received</th>
                                <th data-field="submitter" data-sortable="true" data-filter-control="input">Submitter</th>
                                <th data-field="controller" data-sortable="true" data-filter-control="select">Controller</th>
                                <th data-field="position" data-sortable="true" data-filter-control="select">Position</th>
                                <th data-field="feedback" data-sortable="false" data-filter-control="input">Feedback</th>
                                <th data-field="followup" data-sortable="false" data-filter-control="input" class="text-center">Follow Up</th>
                                <th data-field="actions" data-sortable="false" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($feedback as $f)
                                <tr>
                                    <td>{{ $f->created_at->toEuropeanDateTime() }}</td>
                                    <td><a href="{{ route('user.show', $f->submitter->id) }}">{{ $f->submitter->name }} ({{ $f->submitter_user_id }})</a></td>
                                    <td>
                                        @isset($f->referenceUser)
                                            <a href="{{ route('user.show', $f->referenceUser) }}">{{ $f->referenceUser->name }} ({{ $f->referenceUser->id }})</a>
                                        @else
                                            N/A
                                        @endisset
                                    </td>
                                    <td>
                                        @isset($f->referencePosition)
                                            {{ $f->referencePosition->callsign }}
                                        @else
                                            N/A
                                        @endisset
                                    </td>
                                    <td>
                                        {!! nl2br($f->feedback) !!}
                                    </td>
                                    <td class="text-center">
                                        @if($f->followup)
                                            <i class="fas fa-check text-success"></i>
                                        @else
                                            <i class="fas fa-times text-muted"></i>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @can('viewFeedback', \App\Models\ManagementReport::class)
                                            @if(! $f->reply_sent)
                                                <button type="button" class="btn btn-sm btn-success open-positive-feedback" data-feedback-id="{{ $f->id }}" title="Preview & send positive feedback">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            @else
                                                <span class="text-success" title="Positive reply sent"><i class="fas fa-check-circle"></i></span>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
        <!-- Modal for previewing positive feedback -->
        <div class="modal fade" id="positiveFeedbackModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Preview positive feedback</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="positiveFeedbackModalBody">
                        <div class="text-center py-5">Loading preview…</div>
                    </div>
                    <div class="modal-footer">
                        <form id="positiveFeedbackModalForm" method="POST" action="{{ route('feedback.reply') }}">
                            @csrf
                            <input type="hidden" name="feedback" id="positiveFeedbackModalInput" value="">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Send reply</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function(){
        var modalEl = document.getElementById('positiveFeedbackModal');
        var bsModal = new window.bootstrap.Modal(modalEl);

        var previewUrlTemplate = "{{ route('feedback.preview', ['feedback' => '__ID__']) }}";

        document.addEventListener('click', function(e){
                var btn = e.target.closest('.open-positive-feedback');
                if(!btn) return;
                e.preventDefault();

                var id = btn.getAttribute('data-feedback-id');
                var url = previewUrlTemplate.replace('__ID__', encodeURIComponent(id));

                // show loader
                document.getElementById('positiveFeedbackModalBody').innerHTML = '<div class="text-center py-5">Loading preview…</div>';
                document.getElementById('positiveFeedbackModalInput').value = id;
                bsModal.show();

                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
                        .then(function(res){ if(!res.ok) throw res; return res.json(); })
                        .then(function(data){
                                document.getElementById('positiveFeedbackModalBody').innerHTML = data.html || '<div class="text-danger">Preview failed</div>';
                        })
                        .catch(function(){
                                document.getElementById('positiveFeedbackModalBody').innerHTML = '<div class="text-danger">Preview failed</div>';
                        });
        });
});
</script>
@endsection

@endsection
