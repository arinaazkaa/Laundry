<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Promo;

class TransactionCalculator
{
    public function calculate(array $items, ?Promo $promo, bool $usePickup, bool $useDelivery, ?Customer $customer = null): array
    {
        $subtotal = 0;
        $totalWeight = 0;

        foreach ($items as $item) {
            $subtotal += $item['qty'] * $item['price_per_unit'];
            if ($item['unit_type'] === 'kg') {
                $totalWeight += $item['qty'];
            }
        }

        $deliveryFee = 0;
        if ($usePickup && setting('pickup_enabled')) {
            $deliveryFee += (float) setting('pickup_fee', 0);
        }
        if ($useDelivery && setting('delivery_enabled')) {
            $deliveryFee += (float) setting('delivery_fee', 0);
        }

        $freeOngkirApplied = false;
        if (setting('free_ongkir_enabled') && $totalWeight >= (float) setting('free_ongkir_min_weight', 0)) {
            $deliveryFee = 0;
            $freeOngkirApplied = true;
        }

        $discount = 0;
        $memberDiscountApplied = false;

        // Diskon promo (kalau ada & lebih besar dari diskon member, pakai yang lebih menguntungkan pelanggan)
        if ($promo && $promo->isUsable($subtotal, $totalWeight)) {
            $discount = $promo->calculateDiscount($subtotal);
            if ($promo->type === 'gratis_ongkir') {
                $deliveryFee = 0;
                $freeOngkirApplied = true;
            }
        }

        // Diskon otomatis untuk member (hanya jika tidak sedang pakai promo lain, sesuai aturan "promo tidak boleh bertumpuk")
        if ($customer && $customer->is_member && $discount === 0) {
            $memberDiscountPercent = (float) setting('member_discount_percent', 0);
            if ($memberDiscountPercent > 0) {
                $discount = round($subtotal * ($memberDiscountPercent / 100));
                $memberDiscountApplied = true;
            }
        }

        $total = max(0, $subtotal - $discount + $deliveryFee);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'delivery_fee' => $deliveryFee,
            'total' => $total,
            'total_weight' => $totalWeight,
            'free_ongkir_applied' => $freeOngkirApplied,
            'member_discount_applied' => $memberDiscountApplied,
        ];
    }
}