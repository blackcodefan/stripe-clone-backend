@component('mail::message')
# Wij hebben uw afspraak ontvangen

U heeft een afspraak geplaatst voor:<br />
<strong>{{ $appointment->appointment_at->format('d-m-Y H:i') }}</strong>

<strong>Uw gegevens</strong><br />
<strong>Soort afspraak:</strong> {{ ($appointment->type == 1) ? 'Halen' : 'Terugbrengen' }}<br />
<strong>Objectnummer:</strong> {{ $appointment->object->object_id }}<br />
<strong>Kenteken:</strong> {{ $appointment->object->license_plate }}<br />
<strong>E-mailadres:</strong> {{ $appointment->object->customer->email }}<br />
@if($appointment->note)
<strong>Notitie:</strong> <br />
{{ $appointment->note }}
@endif

Met vriendelijke groet,<br >
{{ $appointment->object->customer->company->name }}<br >
{{ $appointment->object->customer->company->email }}

@endcomponent
