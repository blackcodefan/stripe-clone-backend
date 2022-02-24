<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Cashier\Cashier;
use App\Models\Customer;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $customer_uuid = $request->query('customer', false);
        $starting_after = $request->query('starting_after', 0);

        if ($customer_uuid == "undefined") {
            $params = ["limit" => 100];
            if ($starting_after != 0) {
                $params['starting_after'] = $starting_after;
            }
            $invoices = Cashier::stripe()->invoices->all($params);

            return response(['result' => $invoices]);
        } else {
            $customer = Customer::whereUuid($customer_uuid)->first();
            $invoices = $customer->invoices(true);

            return response(['result' => $invoices]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $data = $request->input();
            $customer = Customer::whereUuid($data['customer'])->first();

            foreach ($data['products'] as $item) {
                if (isset($item['price']) && $item['quantity'] >= 1) {
                    $items[] = $item;

                    Cashier::stripe()->invoiceItems->create([
                        'customer' => $customer->stripe_id,
                        'amount' => $item['isOneTime'] ? $item['amount'] * 100 : $item['amount'],
                        'currency' => $item['isOneTime'] ? 'eur' : $item['currency'],
                        'description' => $item['isOneTime'] ? $item['price'] : $item['description'],
                        'tax_rates' => [
                            $item['tax_rate']
                        ],
                    ]);
                }
            }

            $invoice = Cashier::stripe()->invoices->create([
                'customer' => $customer->stripe_id,
                'collection_method' => 'send_invoice',
                'auto_advance' => true,
                'days_until_due' => $request->due_days
            ]);

            Cashier::stripe()->invoices->sendInvoice(
                $invoice['id'],
                []
            );

            return response(['result' => $invoice]);
        } catch (\Throwable $th) {
            return response(['err' => $th->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $status = $request->query('status');
        $invoice = null;
        if ($status == "void") {
            $invoice = Cashier::stripe()->invoices->voidInvoice(
                $id,
                []
            );
        } else if ($status == "paid") {
            $invoice = Cashier::stripe()->invoices->pay(
                $id,
                ['paid_out_of_band' => true]
            );
        } else if ($status == "uncollectible") {
            $invoice = Cashier::stripe()->invoices->markUncollectible(
                $id,
                []
            );
        }

        return response(['result' => $invoice]);
    }

    public function send($id)
    {
        Cashier::stripe()->invoices->sendInvoice(
            $id,
            []
        );

        return response(['result' => true]);
    }
}
