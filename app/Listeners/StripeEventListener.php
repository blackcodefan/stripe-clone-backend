<?php

namespace App\Listeners;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use App\Events\WebhookReceived;
use Carbon\Carbon;

use App\Models\Subscription;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Price;
use App\Models\Company;

class StripeEventListener
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle received Stripe webhooks.
     *
     * @param  \App\Events\WebhookReceived  $event
     * @return void
     */
    public function handle(WebhookReceived $event)
    {
        error_log(' - event->payload->type: ' . $event->company_id . " <> " . $event->payload['type'] . '\n');
        $company_uuid = $event->company_id;
        $company = Company::whereUuid($company_uuid)->first();
        $company_id = $company ? $company->id : null;

        switch ($event->payload['type']) {
                // BEGIN Subscription
            case 'customer.subscription.created':
                $data = $event->payload['data']['object'];
                $firstItem = $data['items']['data'][0];
                $isSinglePrice = count($data['items']['data']) === 1;

                if (isset($data['trial_end'])) {
                    $trialEndsAt = Carbon::createFromTimestamp($data['trial_end']);
                } else {
                    $trialEndsAt = null;
                }

                $customer = Customer::where('stripe_id', $data['customer'])->first();
                $subscription = [
                    "user_id" => $customer->id,
                    'name' => isset($data['metadata']['name']) ? $data['metadata']['name'] : "",
                    'stripe_id' => $data['id'],
                    'stripe_status' => $data['status'],
                    'stripe_price' => $isSinglePrice ? $firstItem['price']['id'] : null,
                    'quantity' => $isSinglePrice && isset($firstItem['quantity']) ? $firstItem['quantity'] : null,
                    'trial_ends_at' => $trialEndsAt,
                    'ends_at' => null,
                ];

                Subscription::create($subscription);
                break;

            case 'customer.subscription.updated':
                $data = $event->payload['data']['object'];
                $firstItem = $data['items']['data'][0];
                $isSinglePrice = count($data['items']['data']) === 1;

                if (isset($data['trial_end'])) {
                    $trialEndsAt = Carbon::createFromTimestamp($data['trial_end']);
                } else {
                    $trialEndsAt = null;
                }

                $customer = Customer::where('stripe_id', $data['customer'])->first();
                $subscription = [
                    "user_id" => $customer->id,
                    'name' => isset($data['metadata']['name']) ? $data['metadata']['name'] : "",
                    'stripe_id' => $data['id'],
                    'stripe_status' => $data['status'],
                    'stripe_price' => $isSinglePrice ? $firstItem['price']['id'] : null,
                    'quantity' => $isSinglePrice && isset($firstItem['quantity']) ? $firstItem['quantity'] : null,
                    'trial_ends_at' => $trialEndsAt,
                    'ends_at' => $data['ended_at'],
                ];

                Subscription::updateOrInsert(['stripe_id' => $subscription['stripe_id']], $subscription);
                break;

            case 'customer.subscription.deleted':
                $data = $event->payload['data']['object'];
                Subscription::where('stripe_id', $data['id'])->update(['ends_at' => $data['ended_at']]);
                break;

                // BEGIN Customer
            case 'customer.created':
//                $data = $event->payload['data']['object'];
//                $name = $data['name'] ? $data['name'] : "";
//                $segs = explode(" ", $name);
//
//                $customer = [
//                    "stripe_id" => $data['id'],
//                    "email" => $data['email'],
//                    "phone" => $data['phone'],
//                    "firstname" => count($segs) > 0 ? $segs[0] : "",
//                    "lastname" => count($segs) > 1 ? $segs[1] : "",
//                    "city" => $data['address'],
//                    "company_id" => $company_id,
//                    "uuid" => $this->GUID(),
//                ];
//
//                Customer::create($customer);
//                break;

            case 'customer.updated':
                $data = $event->payload['data']['object'];
                $name = $data['name'] ? $data['name'] : "";
                $segs = explode(" ", $name);

                $customer = [
                    "stripe_id" => $data['id'],
                    "email" => $data['email'],
                    "phone" => $data['phone'],
                    "firstname" => count($segs) > 0 ? $segs[0] : "",
                    "lastname" => count($segs) > 1 ? $segs[1] : "",
                    "city" => $data['address'],
                    "company_id" => $company_id,
                ];

                Customer::updateOrInsert(['stripe_id' => $customer['stripe_id']], $customer);
                break;
                // END Customer

                // BEGIN Price
            case 'price.created':
                $data = $event->payload['data']['object'];
                $price = [
                    "stripe_id" => $data['id'],
                    "active" => $data['active'],
                    "created" => $data['created'],
                    "currency" => $data['currency'],
                    "livemode" => $data['livemode'],
                    "metadata" => json_encode($data['metadata']),
                    "product" => $data['product'],
                    "aggregate_usage" => $data['recurring']['aggregate_usage'],
                    "interval" => $data['recurring']['interval'],
                    "interval_count" => $data['recurring']['interval_count'],
                    "usage_type" => $data['recurring']['usage_type'],
                    "tiers_mode" => $data['tiers_mode'],
                    "type" => $data['type'],
                    "unit_amount" => $data['unit_amount'],
                    "unit_amount_decimal" => $data['unit_amount_decimal'],
                    "nickname" => $data['nickname'],
                    "company_id" => $company_id,
                ];

                Price::create($price);
                break;

            case 'price.updated':
                $data = $event->payload['data']['object'];
                $price = [
                    "stripe_id" => $data['id'],
                    "active" => $data['active'],
                    "created" => $data['created'],
                    "currency" => $data['currency'],
                    "livemode" => $data['livemode'],
                    "metadata" => json_encode($data['metadata']),
                    "product" => $data['product'],
                    "aggregate_usage" => $data['recurring']['aggregate_usage'],
                    "interval" => $data['recurring']['interval'],
                    "interval_count" => $data['recurring']['interval_count'],
                    "usage_type" => $data['recurring']['usage_type'],
                    "tiers_mode" => $data['tiers_mode'],
                    "type" => $data['type'],
                    "unit_amount" => $data['unit_amount'],
                    "unit_amount_decimal" => $data['unit_amount_decimal'],
                    "nickname" => $data['nickname'],
                    "company_id" => $company_id,
                ];

                Price::updateOrInsert(['stripe_id' => $price['stripe_id']], $price);
                break;

            case 'price.deleted':
                $data = $event->payload['data']['object'];
                Price::where('stripe_id', $data['id'])->delete();
                break;
                // END Price

                // BEGIN Product
            case 'product.created':
                $data = $event->payload['data']['object'];
                $product = [
                    "stripe_id" => $data['id'],
                    "name" => $data['name'],
                    "active" => $data['active'],
                    "created" => $data['created'],
                    "updated" => $data['updated'],
                    "description" => $data['description'],
                    "livemode" => $data['livemode'],
                    "metadata" => json_encode($data['metadata']),
                    "images" => json_encode($data['images']),
                    "statement_descriptor" => $data['statement_descriptor'],
                    "url" => $data['url'],
                    "package_dimensions" => $data['package_dimensions'],
                    "shippable" => $data['shippable'],
                    "tax_code" => $data['tax_code'],
                    "unit_label" => $data['unit_label'],
                    "company_id" => $company_id,
                ];

                Product::create($product);
                break;

            case 'product.updated':
                $data = $event->payload['data']['object'];
                $product = [
                    "stripe_id" => $data['id'],
                    "name" => $data['name'],
                    "active" => $data['active'],
                    "created" => $data['created'],
                    "updated" => $data['updated'],
                    "description" => $data['description'],
                    "livemode" => $data['livemode'],
                    "metadata" => json_encode($data['metadata']),
                    "images" => json_encode($data['images']),
                    "statement_descriptor" => $data['statement_descriptor'],
                    "url" => $data['url'],
                    "package_dimensions" => $data['package_dimensions'],
                    "shippable" => $data['shippable'],
                    "tax_code" => $data['tax_code'],
                    "unit_label" => $data['unit_label'],
                    "company_id" => $company_id,
                ];

                Product::updateOrInsert(['stripe_id' => $product['stripe_id']], $product);
                break;

            case 'product.deleted':
                $data = $event->payload['data']['object'];
                Product::where('stripe_id', $data['id'])->delete();
                break;
                // END Product

            default:
                # code...
                break;
        }
    }
    public function GUID()
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
