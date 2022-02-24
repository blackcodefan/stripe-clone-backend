<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Cashier\Cashier;
use App\Models\Subscription;
use App\Models\Customer;
use Illuminate\Support\Facades\Date;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $customer_uuid = $request->query('customer');
        $customer = Customer::whereUuid($customer_uuid)->first();
        $subscriptions = [];

        if (!$customer->stripe_id) {
            return response(['result' => []]);
        }

        $stripeSubscriptions = Cashier::stripe()->subscriptions->all(['customer' => $customer->stripe_id, 'status' => 'all']);
        // return response(['result' => $stripeSubscriptions['data']]);

        for ($i = 0; $i < count($stripeSubscriptions['data']); $i++) {
            $stripeSubscription = $stripeSubscriptions['data'][$i];
            if ($stripeSubscription['schedule'] == null) {
                $items = $stripeSubscription['items']['data'];
                $products = [];
                foreach ($items as $item) {
                    $products[] = Cashier::stripe()->products->retrieve($item['price']['product']);
                }
                $stripeSubscription['products'] = $products;
                $subscriptions[] = $stripeSubscription;
            }
        }

        $subScheduleds = Cashier::stripe()->subscriptionSchedules->all([
            'customer' => $customer->stripe_id,
            'scheduled' => true // started scheduled will be part of GET subscriptions
        ]);
        // return response(['result' => $subScheduleds['data']]);

        foreach ($subScheduleds->data as $subSchedule) {
            $items = $subSchedule['phases'][0]['items'];

            $products = [];
            $prices = ["data" => []];

            foreach ($items as $item) {
                $price = Cashier::stripe()->prices->retrieve($item['price']);
                $price['quantity'] = $item['quantity'];
                $prices['data'][] = $price;
                $products[] = Cashier::stripe()->products->retrieve($price['product']);
            }
            $subSchedule->products = $products;
            $subSchedule->items = $prices;
            $subscriptions[] = $subSchedule;
        }

        return response(['result' => $subscriptions]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
//         $data = $request->input();
//         return response(['result' => $data]);
        try {
            $stripe = Cashier::stripe();

            $data = $request->input();
            $customer = Customer::whereUuid($data['customer'])->first();

            // create items array with prices
            $items = [];
            foreach ($data['products'] as $item) {
                if (isset($item['price']) && $item['quantity'] >= 1) {
                    $items[] = $item;
                }
            }

            // initial subscription option
            $sub_options = [
                'customer' => $customer->stripe_id,
                'items' => $items,
                "collection_method" => "send_invoice",
                "days_until_due" => 30,
                'metadata' => $data['metadata'],
                'default_tax_rates' => [
                    $data['tax_rate']
                ]
            ];

            // create a subscription if user selected 'per_direct' for start_date and 'never' for end_date
            if ($data['start_date'] == "per_direct" && $data['end_date'] == "never") {
                // call stripe api to create subscription
                $res = $stripe->subscriptions->create($sub_options);
            } // create a subscription schedule if user selected dates
            else {
                // initial option for subscription schedule
                $sub_schedule_options = [
                    'customer' => $customer->stripe_id,
                    'start_date' => Date::now()->unix(),
                    'phases' => [
                        [
                            'items' => $items,
                            "collection_method" => "send_invoice",
                            "invoice_settings" => [
                                "days_until_due" => 30,
                            ],
                            'default_tax_rates' => [
                                $data['tax_rate']
                            ]
                        ]
                    ],
                    'metadata' => [
                        'start_date' => $data['start_date'],
                        'end_date' => $data['end_date'],
                    ]
                ];

                // set start_date if user selected 'first_day_of_new_month' or 'custom_date'
                if ($data['start_date'] == "first_day_of_new_month" || $data['start_date'] == "custom_date") {
                    $sub_schedule_options["start_date"] = $data['start_custom_date'];
                    $sub_schedule_options["metadata"]["start_custom_date"] = $data['start_custom_date'];
                }

                // set end_date
                if ($data['end_date'] == "custom_date") {
                    $sub_schedule_options['phases'][0]["end_date"] = $data['end_custom_date'];
                    $sub_schedule_options["metadata"]["end_custom_date"] = $data['end_custom_date'];
                } else if ($data['end_date'] == "after_x_cycle") {
                    $sub_schedule_options['phases'][0]["iterations"] = $data['after_x_cycle'];
                    $sub_schedule_options["metadata"]["after_x_cycle"] = $data['after_x_cycle'];
                }

                // call stripe api to create subscription schedule
                $res = $stripe->subscriptionSchedules->create($sub_schedule_options);
            }

            return response(['result' => true]);
        } catch (\Throwable $th) {
            return response(['err' => $th->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $subscription = [];
        if (strpos($id, "sched") == false) {
            $subscription = Cashier::stripe()->subscriptions->retrieve(
                $id,
                []
            );
        } else {
            $subscription = Cashier::stripe()->subscriptionSchedules->retrieve(
                $id,
                []
            );
            $items = $subscription['phases'][0]['items'];

            $prices = ["data" => []];

            foreach ($items as $item) {
                $price = Cashier::stripe()->prices->retrieve($item['price']);
                $temp = ['price' => $price, 'quantity' => $item['quantity']];
                $prices['data'][] = $temp;
            }
            $subscription['items'] = $prices;
        }


        $customer = Customer::where('stripe_id', $subscription['customer'])->first();
        if ($customer) {
            $subscription['customer_uuid'] = $customer->uuid;
        }
        return response(['result' => $subscription]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $stripe = Cashier::stripe();
            $data = $request->input();
            if (strpos($id, "sched") == false) {
                $subscription = $this->stripe->subscriptions->retrieve($id);

                $customer_stripe_id = $data['customer'];
                $items = [];

                foreach ($data['products'] as $product) {
                    if (isset($product['price']) && $product['quantity'] >= 1) {
                        $exiting = false;
                        foreach ($subscription['items']['data'] as $item) {
                            if ($item['price']['id'] == $product['price']) {
                                $exiting = true;
                            }
                        }
                        if ($exiting == false) {
                            $items[] = $product;
                        }
                    }
                }
                // return response(['result' => $items]);

                $res = $this->stripe
                    ->subscriptions
                    ->update($id, [
                        'items' => $items,
//                        'trial_period_days' => 1
                    ]);

                return response(['result' => $res]);
            } else {
                $subSched = $stripe->subscriptionSchedules->retrieve(
                    $id,
                    []
                );

                $items = [];
                foreach ($data['products'] as $product) {
                    if (isset($product['price']) && $product['quantity'] >= 1) {
                        $items[] = $product;
                    }
                }

                $sub_schedule_options = [
                    'phases' => [
                        [
                            'start_date' => $subSched['phases'][0]['start_date'],
                            'items' => $items,
                            "collection_method" => "send_invoice",
                            "invoice_settings" => [
                                "days_until_due" => 30,
                            ]
                        ]
                    ],
                    'metadata' => [
                        'start_date' => $subSched['metadata']['start_date'],
                        'end_date' => $data['end_date'],
                    ]
                ];

                // set start_date if user selected 'first_day_of_new_month' or 'custom_date'
                // if ($data['start_date'] == "first_day_of_new_month" || $data['start_date'] == "custom_date") {
                //     $sub_schedule_options["metadata"]["start_custom_date"] = $data['start_custom_date'];
                // }

                // set end_date
                if ($data['end_date'] == "custom_date") {
                    $sub_schedule_options['phases'][0]["end_date"] = $data['end_custom_date'];
                    $sub_schedule_options["metadata"]["end_custom_date"] = $data['end_custom_date'];
                } else if ($data['end_date'] == "after_x_cycle") {
                    $sub_schedule_options['phases'][0]["iterations"] = $data['after_x_cycle'];
                    $sub_schedule_options["metadata"]["after_x_cycle"] = $data['after_x_cycle'];
                }

                $stripe->subscriptionSchedules->update(
                    $id,
                    $sub_schedule_options
                );
                return response(['status' => 'ok']);
            }
        } catch (\Throwable $th) {
            throw $th;
            return response(['err' => $th->getMessage()], 500);
        }
    }


    /** ============ Cancel subscription =========
     * Invoice always has been created when subscription start
     * So once subscription started, it always has invoice
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response|\Stripe\SubscriptionSchedule
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function destroy(Request $request)
    {
        $params = $request->all();
        $id = $params['id'];
        $cancel_type = $params['cancel_type'];
        $refund_policy = $params['refund_policy'];

        // is it a scheduled subscription?
        if ($request->post('type') == 'subscription_schedule') {

            $subscription_schedule = Cashier::stripe()
                ->subscriptionSchedules
                ->retrieve($id);

            if ($subscription_schedule->status == 'not_started'
                || $subscription_schedule->status == 'active'
            ) {
                return $this->cancelScheduleNow($id);
            }
            return $subscription_schedule;

        } else {

            if ($cancel_type === 'immediate') {
                if ($refund_policy === 'no_refund') {
                    return $this->cancelNow($id);
                } else if ($refund_policy === 'last_payment') {
                    return $this->cancelNowWithRefund($id);
                } else if ($refund_policy === 'prorated_amount') {
                    return $this->cancelNowWithProratedRefund($id);
                }
            } else if ($cancel_type === 'end') {
                return $this->cancelAtEndOfPeriod($id);
            } else if ($cancel_type === 'custom') {
                return $this->cancelAtCustomDate($id, $params['cancel_at']);
            }
        }
        return response()->json(['result' => false], 400);
    }

    /** Cancel subscription at the end of its period
     * No refund is allowed here. It's only possible from stripe dashboard
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Stripe\Exception\ApiErrorException
     */
    private function cancelAtEndOfPeriod($id)
    {
        $subscription = Cashier::stripe()
            ->subscriptions
            ->update($id,
                [
                    'cancel_at_period_end' => true
                ]
            );
        return response($subscription);
    }

    /** Cancel subscription at the custom date
     *  Refund is not allowed here. It's only possible from stripe dashboard
     * @param $id
     * @param $timestamp
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Stripe\Exception\ApiErrorException
     */
    private function cancelAtCustomDate($id, $timestamp)
    {
        $subscription = Cashier::stripe()
            ->subscriptions
            ->update($id,
                [
                    'cancel_at' => $timestamp
                ]
            );
        return response($subscription);
    }

    /** Cancel subscription without refund
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Stripe\Exception\ApiErrorException
     */
    private function cancelNow($id)
    {
        $subscription = Cashier::stripe()->subscriptions->cancel($id);
        return response($subscription);
    }

    /** Cancel subscription with whole refund of latest charge
     * Only available when invoice (latest) status is paid
     * Refunds last invoice whole charge
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Stripe\Exception\ApiErrorException
     */
    private function cancelNowWithRefund($id)
    {
        $stripe = Cashier::stripe();
        $subscription = $stripe->subscriptions->retrieve($id);

        $latest_invoice = $stripe
            ->invoices
            ->retrieve($subscription->latest_invoice);

        if ($latest_invoice->status != 'paid')
            return response($subscription, 400);

        $stripe->refunds->create([
            'charge' => $latest_invoice->charge,
            'amount' => $latest_invoice->total
        ]);


        return $this->cancelNow($id);
    }

    /** Cancel subscription with prorated refund of latest charge
     * Only available when invoice (latest) status is paid
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Stripe\Exception\ApiErrorException
     */
    private function cancelNowWithProratedRefund($id)
    {
        $stripe = Cashier::stripe();

        $subscription = $stripe->subscriptions->retrieve($id);

        $latest_invoice = $stripe
            ->invoices
            ->retrieve($subscription->latest_invoice);

        if ($latest_invoice->status != 'paid')
            return response($subscription, 400);

        $subscription_items = [];

        foreach ($subscription->items->data as $item) {
            $subscription_items[] = array(
                "id" => $item->id,
                "price" => $item->price->id,
                "quantity" => 0
            );
        }

        $upcoming_prorated_invoice = $stripe->invoices->upcoming([
            "customer" => $subscription->customer,
            "subscription" => $subscription->id,
            "subscription_items" => $subscription_items
        ]);

        $prorated_amount = 0;

        foreach ($upcoming_prorated_invoice->lines->data as $invoice) {
            if ($invoice->type == "invoiceitem") {

                $prorated_amount = ($invoice->amount < 0) ? abs($invoice->amount) : 0;

                break;

            }
        }

        if ($prorated_amount > 0) {
            $stripe->refunds->create([
                'charge' => $latest_invoice->charge,
                'amount' => $prorated_amount
            ]);
        }

        return $this->cancelNow($id);
    }

    private function cancelScheduleNow($id)
    {
        $subscription_schedule = Cashier::stripe()->subscriptionSchedules->cancel($id);
        return response($subscription_schedule);
    }
}
