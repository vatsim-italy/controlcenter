@extends('layouts.app')

@section('title', 'Preview positive feedback')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card mb-4">
            <div class="card-header">
                Preview positive feedback to {{ $feedback->submitter->name }}
            </div>
            <div class="card-body">
                <div class="mb-3">
                    @include('mail.positive_feedback', [
                        'firstName' => $feedback->submitter->first_name,
                        'sender' => auth()->user()->first_name,
                    ])
                </div>
                <form method="POST" action="{{ route('feedback.reply') }}">
                    @csrf
                    <input type="hidden" name="feedback" value="{{ $feedback->id }}">
                    <a href="{{ route('reports.feedback') }}" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-success">Send reply</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
