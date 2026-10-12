<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use App\Models\Service;
use App\Models\Transaction;
use App\Services\TransactionCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Transaction::with(['customer', 'cashier'])
            ->when($request->q, function ($q) use ($request) {
                $q->where('code', 'like', "%{$request->q}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$request->q}%")
                        ->orWhere('phone', 'like', "%{$request->q}%"));
            })
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15);

        return view('transactions.index', compact('transactions'));
    }

    public function processing()
    {
        $transactions = Transaction::whereIn('status', ['diproses', 'dicuci', 'dikeringkan', 'disetrika'])
            ->latest()->paginate(15);
        return view('transactions.index', compact('transactions'));
    }

    public function ready()
    {
        $transactions = Transaction::where('status', 'siap_diambil')->latest()->paginate(15);
        return view('transactions.index', compact('transactions'));
    }

    public function history()
    {
        $transactions = Transaction::whereIn('status', ['selesai', 'dibatalkan'])->latest()->paginate(15);
        return view('transactions.index', compact('transactions'));
    }

    public function create()
    {
        $services = Service::active()->get(['id', 'name', 'type', 'price']);

        $promos = Promo::where('is_active', true)
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->get(['id', 'name', 'type', 'value', 'min_transaction', 'min_weight', 'quota', 'used_count']);

        return view('transactions.create', compact('services', 'promos'));
    }

    public function store(Request $request, TransactionCalculator $calculator)
    {
        $data = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.name' => 'required|string',
            'items.*.type' => 'required|in:kiloan,satuan',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.qty' => 'required|numeric|min:0.1',
            'promo_id' => 'nullable|exists:promos,id',
            'use_pickup' => 'boolean',
            'use_delivery' => 'boolean',
            'payment_method' => 'nullable|string',
            'paid_amount' => 'nullable|numeric|min:0',
        ]);

        // Susun ulang item untuk kalkulator (server-side, tidak percaya angka total dari frontend)
        $calcItems = collect($data['items'])->map(fn ($i) => [
            'qty' => $i['qty'],
            'price_per_unit' => $i['price'],
            'unit_type' => $i['type'] === 'kiloan' ? 'kg' : 'pcs',
        ])->toArray();

        $promo = isset($data['promo_id']) ? Promo::find($data['promo_id']) : null;
        $customer = isset($data['customer_id']) ? \App\Models\Customer::find($data['customer_id']) : null;

        $result = $calculator->calculate(
            $calcItems,
            $promo,
            $data['use_pickup'] ?? false,
            $data['use_delivery'] ?? false,
            $customer
        );

        $transaction = DB::transaction(function () use ($data, $result, $promo, $request) {
            $paid = $data['paid_amount'] ?? 0;
            $paymentStatus = 'belum_bayar';
            if ($paid >= $result['total'] && $result['total'] > 0) {
                $paymentStatus = 'lunas';
            } elseif ($paid > 0) {
                $paymentStatus = 'dp';
            }

            $transaction = Transaction::create([
                'customer_id' => $data['customer_id'] ?? null,
                'cashier_id' => Auth::id(),
                'subtotal' => $result['subtotal'],
                'discount' => $result['discount'],
                'delivery_fee' => $result['delivery_fee'],
                'total' => $result['total'],
                'paid_amount' => min($paid, $result['total']),
                'payment_status' => $paymentStatus,
                'payment_method' => $data['payment_method'] ?? null,
                'promo_id' => $promo?->id,
                'use_pickup' => $data['use_pickup'] ?? false,
                'use_delivery' => $data['use_delivery'] ?? false,
                'estimated_done_at' => now()->addDays((int) setting('work_duration_days', 2)),
            ]);

            foreach ($data['items'] as $item) {
                $transaction->items()->create([
                    'service_id' => $item['service_id'] ?? null,
                    'service_name' => $item['name'],
                    'unit_type' => $item['type'] === 'kiloan' ? 'kg' : 'pcs',
                    'qty' => $item['qty'],
                    'price_per_unit' => $item['price'],
                    'subtotal' => $item['qty'] * $item['price'],
                ]);
            }

            if ($paid > 0) {
                $transaction->payments()->create([
                    'amount' => min($paid, $result['total']),
                    'method' => $data['payment_method'] ?? 'cash',
                    'received_by' => Auth::id(),
                ]);
            }

            if ($promo) {
                $promo->increment('used_count');
                $promo->usages()->create([
                    'transaction_id' => $transaction->id,
                    'discount_amount' => $result['discount'],
                ]);
            }

            return $transaction;
        });

        if ($request->wantsJson()) {
            return response()->json(['id' => $transaction->id, 'code' => $transaction->code]);
        }

        return redirect()->route('transactions.show', $transaction)->with('success', 'Transaksi berhasil disimpan.');
    }

    public function preview(Request $request, TransactionCalculator $calculator)
    {
        $data = $request->validate([
            'items' => 'array',
            'items.*.qty' => 'required_with:items|numeric|min:0',
            'items.*.price' => 'required_with:items|numeric|min:0',
            'items.*.type' => 'required_with:items|in:kiloan,satuan',
            'promo_id' => 'nullable|exists:promos,id',
            'use_pickup' => 'boolean',
            'use_delivery' => 'boolean',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        $calcItems = collect($data['items'] ?? [])->map(fn ($i) => [
            'qty' => $i['qty'],
            'price_per_unit' => $i['price'],
            'unit_type' => $i['type'] === 'kiloan' ? 'kg' : 'pcs',
        ])->toArray();

        $promo = isset($data['promo_id']) ? Promo::find($data['promo_id']) : null;
        $customer = isset($data['customer_id']) ? \App\Models\Customer::find($data['customer_id']) : null;

        $subtotal = collect($calcItems)->sum(fn ($i) => $i['qty'] * $i['price_per_unit']);
        $totalWeight = collect($calcItems)->where('unit_type', 'kg')->sum('qty');

        $promoUsable = $promo ? $promo->isUsable($subtotal, $totalWeight) : null;

        $result = $calculator->calculate(
            $calcItems,
            $promo,
            $data['use_pickup'] ?? false,
            $data['use_delivery'] ?? false,
            $customer
        );

        return response()->json([
            ...$result,
            'promo_usable' => $promoUsable,
            'promo_reason' => $promo && !$promoUsable ? $this->promoIneligibleReason($promo, $subtotal, $totalWeight) : null,
        ]);
    }

    private function promoIneligibleReason(Promo $promo, float $subtotal, float $totalWeight): string
    {
        if ($promo->status_label !== 'aktif') {
            return match ($promo->status_label) {
                'belum_dimulai' => 'Promo ini belum dimulai.',
                'kadaluarsa' => 'Promo ini sudah tidak berlaku.',
                'habis_kuota' => 'Promo sudah mencapai batas penggunaan.',
                'nonaktif' => 'Promo sedang dinonaktifkan.',
                default => 'Promo tidak dapat digunakan saat ini.',
            };
        }

        if ($promo->start_time && $promo->end_time) {
            $now = now()->format('H:i:s');
            if ($now < $promo->start_time || $now > $promo->end_time) {
                return 'Promo ini hanya berlaku pukul ' . substr($promo->start_time, 0, 5) . ' - ' . substr($promo->end_time, 0, 5) . '.';
            }
        }

        if ($promo->min_transaction && $subtotal < $promo->min_transaction) {
            return 'Minimal transaksi Rp ' . number_format($promo->min_transaction, 0, ',', '.') . ' belum terpenuhi.';
        }

        if ($promo->min_weight && $totalWeight < $promo->min_weight) {
            return 'Minimal berat ' . $promo->min_weight . ' kg belum terpenuhi.';
        }

        return 'Promo tidak dapat digunakan saat ini.';
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['customer', 'cashier', 'items', 'payments', 'promo']);
        return view('transactions.show', compact('transaction'));
    }
    public function printNota(Transaction $transaction)
    {
        $transaction->load(['customer', 'cashier', 'items']);
        $qrCodeSvg = QrCode::size(120)->generate(route('tracking.show', $transaction->code));

        $pdf = Pdf::loadView('transactions.nota', compact('transaction', 'qrCodeSvg'))
            ->setPaper([0, 0, 226.77, 800], 'portrait');

        return $pdf->stream("nota-{$transaction->code}.pdf");
    }

    public function downloadNota(Transaction $transaction)
    {
        $transaction->load(['customer', 'cashier', 'items']);
        $qrCodeSvg = QrCode::size(120)->generate(route('tracking.show', $transaction->code));

        $pdf = Pdf::loadView('transactions.nota', compact('transaction', 'qrCodeSvg'))
            ->setPaper([0, 0, 226.77, 800], 'portrait');

        return $pdf->download("nota-{$transaction->code}.pdf");
    }

    public function updateStatus(Request $request, Transaction $transaction)
    {
        $request->validate([
            'status' => 'required|in:menunggu,diproses,dicuci,dikeringkan,disetrika,siap_diambil,selesai,dibatalkan',
        ]);

        $transaction->update(['status' => $request->status]);

        return back()->with('success', 'Status transaksi diperbarui menjadi ' . str_replace('_', ' ', $request->status) . '.');
    }

    public function cancel(Transaction $transaction)
    {
        $transaction->update(['status' => 'dibatalkan']);
        return back()->with('success', 'Transaksi dibatalkan.');
    }

    public function processQueue()
    {
        $transactions = Transaction::whereNotIn('status', ['selesai', 'dibatalkan'])
            ->orderBy('created_at')
            ->get();

        return view('transactions.process-queue', compact('transactions'));
    }
}