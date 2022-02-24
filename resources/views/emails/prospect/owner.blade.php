@component('mail::message')
# Er is een nieuwe inschrijving

We hebben deze gegevens ontvangen:<br />
<strong>Naam:</strong> {{ $prospect->firstname }} {{ $prospect->lastname }} <br />
<strong>Adres:</strong> {{ $prospect->street }} {{ $prospect->number }}<br />
<strong>Postcode:</strong> {{ $prospect->zipcode }}<br />
<strong>Woonplaats:</strong> {{ $prospect->city }}<br />
<strong>Telefoonnummer:</strong> {{ $prospect->phone }}<br />
<strong>E-mailadres:</strong> {{ $prospect->email }}<br /><br />

<strong>Object type:</strong> {{ $prospect->object_type->name }}<br />
<strong>Model:</strong> {{ $prospect->brand }} {{ $prospect->type }}<br />
<strong>Kenteken:</strong> {{ $prospect->license_plate }}<br />
<strong>Lengte:</strong> {{ $prospect->length }}<br />
<strong>Breedte:</strong> {{ $prospect->width }}<br />
<strong>Gewenste datum van brengen:</strong> {{ $prospect->delivery_at->format('d-m-Y') }}<br />
@if($prospect->note)
    <strong>Opmerking:</strong><br />{{ $prospect->note }}<br />
@endif

@endcomponent
