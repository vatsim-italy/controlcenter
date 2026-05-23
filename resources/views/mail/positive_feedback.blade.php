@component('mail::message')

Hello {{$firstName}},

We're very pleased to hear that you had a positive experience.
We truly appreciate your feedback and hope to see you again flying in Italy soon!

Thanks,
{{ $sender }}

@endcomponent

