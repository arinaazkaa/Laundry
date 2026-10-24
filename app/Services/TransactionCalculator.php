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

        // Ongkir dasar sesuai Jemput/Antar yang dipilih
        $deliveryFee = 0;
        if ($usePickup && setting('pickup_enabled')) {
            $deliveryFee += (float) setting('pickup_fee', 0);
        }
        if ($useDelivery && setting('delivery_enabled')) {
            $deliveryFee += (float) setting('delivery_fee', 0);
        }

        // Gratis ongkir otomatis. Hanya dihitung kalau memang ada ongkir yang digratiskan
        $freeOngkirApplied = false;
        if ($deliveryFee > 0
            && setting('free_ongkir_enabled')
            && $totalWeight >= (float) setting('free_ongkir_min_weight', 0)) {
            $deliveryFee = 0;
            $freeOngkirApplied = true;
        }

        // Promo (satu transaksi satu promo)
        $discount = 0;
        $promoApplied = false;
        $promoReason = null;

        if ($promo) {
            $promoReason = $promo->ineligibleReason($subtotal, $totalWeight, $customer);

            if ($promoReason === null) {
                if ($promo->type === 'gratis_ongkir') {
                    if ($deliveryFee > 0) {
                        $deliveryFee = 0;
                        $freeOngkirApplied = true;
                        $promoApplied = true;
                    } elseif ($freeOngkirApplied) {
                        $promoReason = 'Ongkir sudah gratis dari aturan gratis ongkir otomatis, promo ini tidak diperlukan.';
                    } else {
                        $promoReason = 'Promo Gratis Ongkir hanya berlaku kalau Jemput atau Antar dipilih.';
                    }
                } else {
                    $discount = $promo->calculateDiscount($subtotal);
                    $promoApplied = $discount > 0;

                    if (! $promoApplied) {
                        $promoReason = 'Promo ini tidak memberi potongan untuk transaksi ini.';
                    }
                }
            }
        }

        // Diskon member otomatis, hanya kalau tidak ada promo yang berlaku
        $memberDiscountApplied = false;
        if ($customer && $customer->is_member && ! $promoApplied) {
            $percent = (float) setting('member_discount_percent', 0);
            if ($percent > 0) {
                $discount = min($subtotal, round($subtotal * ($percent / 100)));
                $memberDiscountApplied = $discount > 0;
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
            'promo_applied' => $promoApplied,
            'promo_reason' => $promoReason,
        ];
    }
}