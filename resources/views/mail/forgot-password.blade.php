@component('mail::message')
# Dear {{$user->username}} 

{{$user->confirmation_code}} use this code to reset your Doc Share account password.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
