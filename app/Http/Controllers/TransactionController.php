<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Promo;
use App\Models\Service;
use App\Models\Transaction;
use App\Services\TransactionCalculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = $this->listTransactions($request);
        return view('transactions.index', compact('transactions'));
    }

    public function processing(Request $request)
    {
        $transactions = $this->listTransactions($request, ['diproses', 'dicuci', 'dikeringkan', 'disetrika']);
        return view('transactions.index', compact('transactions'));
    }

    public function ready(Request $request)
    {
        $transactions = $this->listTransactions($request, ['siap_diambil']);
        return view('transactions.index', compact('transactions'));
    }

    public function history(Request $request)
    {
        $transactions = $this->listTransactions($request, ['selesai', 'dibatalkan']);
        return view('transactions.index', compact('transactions'));
    }

    private function listTransactions(Request $request, ?array $statuses = null)
    {
        return Transaction::with(['customer', 'cashier'])
            ->when($statuses, fn ($q) => $q->whereIn('status', $statuses))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->q, function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('code', 'like', "%{$request->q}%")
                        ->orWhereHas('customer', function ($c) use ($request) {
                            $c->where('name', 'like', "%{$request->q}%")
                                ->orWhere('phone', 'like', "%{$request->q}%");
                        });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    public function create()
    {
        $services = Service::active()->get(['id', 'name', 'type', 'price']);

        $promos = Promo::where('is_active', true)
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->get(['id', 'name', 'type', 'value', 'value_type', 'min_transaction', 'min_weight', 'quota', 'used_count']);

        $totalPromo = Promo::count();

        return view('transactions.create', compact('services', 'promos', 'totalPromo'));
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

        $customer = isset($data['customer_id']) ? Customer::find($data['customer_id']) : null;

        $transaction = DB::transaction(function () use ($data, $calcItems, $customer, $calculator) {
            // Kunci baris promo supaya 2 kasir yang menyimpan bersamaan tidak bisa melewati kuota
            $promo = ! empty($data['promo_id'])
                ? Promo::lockForUpdate()->find($data['promo_id'])
                : null;

            $result = $calculator->calculate(
                $calcItems,
                $promo,
                $data['use_pickup'] ?? false,
                $data['use_delivery'] ?? false,
                $customer
            );

            // Promo dipilih tapi tidak berlaku: tolak dengan alasan jelas, jangan disimpan diam-diam tanpa diskon
            if ($promo && ! $result['promo_applied']) {
                throw ValidationException::withMessages([
                    'promo_id' => $result['promo_reason'] ?? 'Promo tidak dapat digunakan.',
                ]);
            }

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

            if ($transaction->use_pickup) {
                $transaction->pickupDeliveries()->create([
                    'type' => 'pickup',
                    'status' => 'dijadwalkan',
                    'scheduled_at' => now(),
                    'address' => $customer?->address,
                ]);
            }

            if ($transaction->use_delivery) {
                $transaction->pickupDeliveries()->create([
                    'type' => 'delivery',
                    'status' => 'dijadwalkan',
                    'scheduled_at' => $transaction->estimated_done_at,
                    'address' => $customer?->address,
                ]);
            }

            if ($paid > 0) {
                $transaction->payments()->create([
                    'amount' => min($paid, $result['total']),
                    'method' => $data['payment_method'] ?? 'cash',
                    'received_by' => Auth::id(),
                ]);
            }

            // Kuota hanya berkurang kalau promo benar-benar berefek (lolos pengecekan di atas)
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
        $customer = isset($data['customer_id']) ? Customer::find($data['customer_id']) : null;

        $result = $calculator->calculate(
            $calcItems,
            $promo,
            $data['use_pickup'] ?? false,
            $data['use_delivery'] ?? false,
            $customer
        );

        return response()->json([
            ...$result,
            'promo_usable' => $promo ? $result['promo_applied'] : null,
        ]);
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

        // Tugas antar-jemput yang belum selesai ikut dibatalkan
        $transaction->pickupDeliveries()
            ->where('status', '!=', 'selesai')
            ->update(['status' => 'dibatalkan']);

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