<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Promo;

class TransactionCalculator
{
    /**
     * Tiap item: qty, price_per_unit, unit_type (kg|pcs|paket), weight_kg (opsional), package_id (opsional)
     *
     * @param  iterable<Promo>|Promo|null  $promos  Satu promo atau beberapa promo (kalau penggabungan diaktifkan)
     */
    public function calculate(array $items, $promos, bool $usePickup, bool $useDelivery, ?Customer $customer = null): array
    {
        $promos = $promos instanceof Promo ? collect([$promos]) : collect($promos ?? []);
        $promos = $promos->filter()->unique('id')->values();

        $subtotal = 0;
        $packageSubtotal = 0;
        $totalWeight = 0;

        foreach ($items as $item) {
            $lineTotal = $item['qty'] * $item['price_per_unit'];
            $subtotal += $lineTotal;

            if (! empty($item['package_id'])) {
                $packageSubtotal += $lineTotal;
            }

            $totalWeight += $item['weight_kg'] ?? ($item['unit_type'] === 'kg' ? $item['qty'] : 0);
        }

        // Harga paket sudah harga hemat, jadi potongan promo dan member hanya dihitung dari item non-paket
        $discountBase = $subtotal - $packageSubtotal;

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

        // Aturan penggabungan promo
        $stackError = null;
        if ($promos->count() > 1) {
            $notStackable = $promos->first(fn ($p) => ! $p->stackable);

            $stackError = match (true) {
                ! setting('promo_stacking_enabled') => 'Penggabungan promo belum diaktifkan oleh pemilik. Pilih satu promo saja.',
                $promos->count() > 2 => 'Maksimal 2 promo dalam satu transaksi.',
                $notStackable !== null => 'Promo "' . $notStackable->name . '" tidak bisa digabung dengan promo lain.',
                $promos->where('type', 'gratis_ongkir')->count() > 1 => 'Dua promo Gratis Ongkir tidak bisa digabung.',
                default => null,
            };
        }

        // Evaluasi tiap promo
        $results = [];
        $discount = 0;
        $remaining = max(0, $discountBase);
        $anyApplied = false;

        foreach ($promos as $promo) {
            $reason = $stackError ?? $promo->ineligibleReason($subtotal, $totalWeight, $customer);
            $amount = 0;

            if ($reason === null) {
                if ($promo->type === 'gratis_ongkir') {
                    if ($deliveryFee > 0) {
                        $amount = $deliveryFee;
                        $deliveryFee = 0;
                        $freeOngkirApplied = true;
                    } elseif ($freeOngkirApplied) {
                        $reason = 'Ongkir sudah gratis dari aturan gratis ongkir otomatis, promo ini tidak diperlukan.';
                    } else {
                        $reason = 'Promo Gratis Ongkir hanya berlaku kalau Jemput atau Antar dipilih.';
                    }
                } else {
                    $amount = min($promo->calculateDiscount($discountBase), $remaining);

                    if ($amount <= 0) {
                        $reason = ($discountBase <= 0 && $packageSubtotal > 0)
                            ? 'Harga paket sudah hemat, potongan promo tidak berlaku untuk paket.'
                            : 'Promo ini tidak memberi potongan untuk transaksi ini.';
                    } else {
                        $remaining -= $amount;
                        $discount += $amount;
                    }
                }
            }

            $applied = $reason === null;
            $anyApplied = $anyApplied || $applied;

            $results[] = [
                'id' => $promo->id,
                'applied' => $applied,
                'reason' => $reason,
                'amount' => $applied ? $amount : 0,
            ];
        }

        // Diskon member otomatis, hanya kalau tidak ada promo yang berlaku
        $memberDiscountApplied = false;
        if ($customer && $customer->is_member && ! $anyApplied) {
            $percent = (float) setting('member_discount_percent', 0);
            if ($percent > 0) {
                $discount = min($discountBase, round($discountBase * ($percent / 100)));
                $memberDiscountApplied = $discount > 0;
            }
        }

        $total = max(0, $subtotal - $discount + $deliveryFee);
        $failed = collect($results)->first(fn ($r) => ! $r['applied']);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'delivery_fee' => $deliveryFee,
            'total' => $total,
            'total_weight' => $totalWeight,
            'free_ongkir_applied' => $freeOngkirApplied,
            'member_discount_applied' => $memberDiscountApplied,
            'promo_results' => $results,
            'promo_applied' => $promos->isNotEmpty() && $failed === null,
            'promo_reason' => $failed['reason'] ?? null,
        ];
    }
}