<?php

namespace App\Http\Controllers;

use Laravel\Cashier\Cashier;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Price;
use App\Models\Product;

class StripeSyncController extends Controller
{
    public function syncPrices()
    {
        $stripePrices = Cashier::stripe()->prices->all(['limit' => 100]);
        $company_id = Auth::user()->company->id;

        // return response()->json([
        //     'status' => $stripePrices['data'][0]->recurring['aggregate_usage'],
        // ]);

        foreach ($stripePrices['data'] as $data) {
            $price = [
                "stripe_id" => $data->id,
                "active" => $data->active,
                "created" => $data->created,
                "currency" => $data->currency,
                "livemode" => $data->livemode,
                "metadata" => json_encode($data->metadata),
                "product" => $data->product,
                "aggregate_usage" => $data->recurring ? $data->recurring['aggregate_usage'] : null,
                "interval" => $data->recurring ? $data->recurring['interval'] : null,
                "interval_count" => $data->recurring ? $data->recurring['interval_count'] : null,
                "usage_type" => $data->recurring ? $data->recurring['usage_type'] : null,
                "tiers_mode" => $data->tiers_mode,
                "type" => $data->type,
                "unit_amount" => $data->unit_amount,
                "unit_amount_decimal" => $data->unit_amount_decimal,
                "nickname" => $data->nickname,
                "company_id" => $company_id,
            ];

            Price::updateOrInsert(['stripe_id' => $price['stripe_id']], $price);
        }

        return response()->json([
            'status' => 'true',
        ]);
    }

    public function syncProducts()
    {
        $stripeProducts = Cashier::stripe()->products->all(['limit' => 100]);
        $company_id = Auth::user()->company->id;

        foreach ($stripeProducts['data'] as $data) {
            $product = [
                "stripe_id" => $data->id,
                "name" => $data->name,
                "active" => $data->active,
                "created" => $data->created,
                "updated" => $data->updated,
                "description" => $data->description,
                "livemode" => $data->livemode,
                "metadata" => json_encode($data->metadata),
                "images" => json_encode($data->images),
                "statement_descriptor" => $data->statement_descriptor,
                "url" => $data->url,
                "package_dimensions" => $data->package_dimensions,
                "shippable" => $data->shippable,
                "tax_code" => $data->tax_code,
                "unit_label" => $data->unit_label,
                "company_id" => $company_id,
            ];

            Product::updateOrInsert(['stripe_id' => $product['stripe_id']], $product);
        }

        return response()->json([
            'status' => 'true',
        ]);
    }
}
