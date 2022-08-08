@component('mail::message')

{{$msg}}

Cordialement,<br>
{{ config('app.name') }}
@endcomponent
