@extends('layouts.app')

@section('title', 'Feedback')
@section('content')

<div class="row">
    <div class="col-xl-6 col-lg-12 col-md-12 mb-12">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-white">
                    Submit Feedback
                </h6>
            </div>
            <div class="card-body">
                <form action="{!! action('FeedbackController@store') !!}" method="POST">
                    @csrf

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label">VATSIM ID</label>
                            <input class="form-control" type="text" value="{{ Auth::user()->id }}" disabled>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Your name</label>
                            <input class="form-control" type="text" value="{{ Auth::user()->name }}" disabled>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Your email</label>
                            <input class="form-control" type="text" value="{{ Auth::user()->notificationEmail }}" disabled>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label" for="controllers">Controller <small class="form-text"> (Optional)</small></label>
                            <input
                                id="controllers"
                                class="form-control"
                                type="text"
                                name="controller"
                                list="controllersList"
                                multiple="multiple"
                                value="{{ old('controller') }}"
                                >

                            <datalist id="controllersList">
                                @foreach($controllers as $controller)
                                    @browser('isFirefox')
                                        <option>{{ $controller->id }}</option>
                                    @else
                                        <option value="{{ $controller->id }}">{{ $controller->name }}</option>
                                    @endbrowser
                                @endforeach
                            </datalist>

                            @error('controller')
                                <span class="text-danger">{{ $errors->first('controller') }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="positions">Controller's position <small class="form-text"> (Optional)</small></label>
                            <input
                                id="positions"
                                class="form-control"
                                type="text"
                                name="position"
                                list="positionsList"
                                multiple="multiple"
                                value="{{ old('position') }}"
                                >

                            <datalist id="positionsList">
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
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Your feedback</label>
                            <textarea class="form-control" name="feedback" rows="5">{{ old('feedback') }}</textarea>
                            @error('feedback')
                                <span class="text-danger">{{ $errors->first('feedback') }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" type="checkbox" name="visibilityToggle"  id="visibilityToggle" role="switch" checked>
                                <label class="form-check-label" for="visibilityToggle">
                                    <span class="fw-semibold">Make visible to controller</span><br>
                                    <small class="text-muted">Your data will be anonymised</small>
                                </label>
                            </div>

                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="emailToggle" id="emailToggle" role="switch" checked>
                                <label class="form-check-label" for="emailToggle">
                                    <span class="fw-semibold">Receive follow-up email</span>
                                </label>
                            </div>
                        </div>
                    </div>


                    <button type="submit" class="btn btn-success" onclick="handleSubmit(event)">Submit Feedback
                        <div class="submit-spinner spinner-border spinner-border-sm" role="status" style="display: none;">&nbsp;</div>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
@vite('resources/js/vue.js')
<script>
    function handleSubmit(event) {
        event.preventDefault();
        document.querySelector('.submit-spinner').style.display = 'inherit';
        event.target.disabled = true;
        event.target.closest('form').submit();
    }
</script>
@endsection
