@component('mail::message')
# Er is een nieuwe afspraak geplaatst

Er is een afspraak geplaatst voor:<br />
<strong>Soort afspraak:</strong> {{ ($appointment->type == 1) ? 'Halen' : 'Terugbrengen' }}<br />
<strong>Objectnummer:</strong> {{ $appointment->object->object_id }}<br />
<strong>Datum:</strong> {{ $appointment->appointment_at->format('d-m-Y H:i') }}<br />
<strong>Kenteken:</strong> {{ $appointment->object->license_plate }}<br />
<strong>Naam:</strong> {{ $appointment->name }}<br />
<strong>E-mailadres:</strong> {{ $appointment->object->customer->email }}<br />
@if($appointment->note)
<strong>Notitie:</strong> <br />
{{ $appointment->note }}
@endif

@component('mail::button', ['url' => env('APP_URL')])
Ga naar het CRM
@endcomponent

@endcomponent
