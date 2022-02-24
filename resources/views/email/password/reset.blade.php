@component('mail::message')
# {{ __('passwords.email_subject') }}

{{ __('passwords.email_line1') }}

@component('mail::button', ['url' => $url])
{{ __('passwords.email_subject') }}
@endcomponent

@endcomponent
