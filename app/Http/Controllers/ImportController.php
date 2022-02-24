<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImportController extends Controller
{

    public function index()
    {

        //dd(2);

        \Config::set('excel.imports.csv.delimiter', ';');

        //dd(\Storage::get('customers.csv'));
        $import = \Excel::toCollection(new \App\Imports\CustomerObjectImport, 'zwaan.csv')->first();

        $object_type_mapper = [
            'caravan' => 1,
            'bagagewagen' => 4,
            'boot' => 3,
            'vouwwagen' => 2,
            'camper' => 7,
            'caravan breed' => 5,
            'trailer' => 9,
            'auto' => 8,
            'aanhanger' => 10
        ];

        foreach($import as $foo) {

            if(!\App\Models\CustomerObject::whereObjectId($foo['klantnummer'])->whereHas('customer', function($q) {
                $q->where('company_id', 4);
            })->first()) {

                \DB::beginTransaction();

                try {

                    // create customer
                    $customer = \App\Models\Customer::create([
                        'company_id' => 4,
                        'email' => \Illuminate\Support\Str::of($foo['email'])->trim(),
                        'firstname' => $foo['voornaam'],
                        'lastname' => implode(' ', [$foo['tussenvoegsel'], $foo['achternaam']]),
                        'street' => $foo['straatnaam'],
                        'number' => $foo['huisnummer'],
                        'zipcode' => $foo['postcode'],
                        'city' => $foo['woonplaats'],
                        'phone' => $foo['mobiel'],
                        'phone2' => $foo['telefoon'],
                    ]);

                    // create object
                    $customer->objects()->create([
                        'spot' => $foo['locatie'],
                        'object_id' => $foo['klantnummer'],
                        'object_type_id' => ($foo['soort']) ? $object_type_mapper[strtolower(\Illuminate\Support\Str::of($foo['soort']))] : null,
                        'brand' => $foo['merk'],
                        'type' => $foo['type'],
                        'license_plate' => strtoupper($foo['kenteken']),
                        'length' => $foo['lengte'],
                        'width' => $foo['breedte'],
                        'chassis' => $foo['chassisnummer']
                    ]);

                    // add an note?
                    if($foo['opmerking']) {
                        $customer->notes()->create([
                            'note' => $foo['opmerking']
                        ]);
                    }

                    \DB::commit();

                } catch (\Exception $e) {

                    \DB::rollback();
                    dd($e->getMessage());

                }

            }

        }

    }

}