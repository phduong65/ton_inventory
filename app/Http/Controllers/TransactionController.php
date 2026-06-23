<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\Destination;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\TransactionAttachment;
use App\Models\TransactionDetail;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(private TransactionService $service) {}

    public function index(Request $request): View
    {
        $query = Transaction::with(['supplier', 'destination', 'createdBy'])
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->date_from, fn($q) => $q->whereDate('date', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('date', '<=', $request->date_to))
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');

        $transactions = $query->paginate(20)->withQueryString();

        return view('transactions.index', compact('transactions'));
    }

    public function create(Request $request): View
    {
        $type         = $request->get('type', 'IN');
        $suppliers    = Supplier::orderBy('name')->get();
        $destinations = Destination::all();
        $products     = Product::active()->with('inventory')->orderBy('name')->get();

        return view('transactions.create', compact('type', 'suppliers', 'destinations', 'products'));
    }

    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $this->authorize('create-transactions');

        $data = $request->validated();

        $transaction = Transaction::create([
            'code'           => Transaction::generateCode($data['type']),
            'type'           => $data['type'],
            'status'         => 'draft',
            'supplier_id'    => $data['supplier_id'] ?? null,
            'destination_id' => $data['destination_id'] ?? null,
            'created_by'     => auth()->id(),
            'date'           => $data['date'],
            'note'           => $data['note'] ?? null,
        ]);

        foreach ($data['details'] as $detail) {
            $qty      = (float) $detail['qty'];
            $price    = (float) ($detail['price'] ?? 0);
            $discount = (float) ($detail['discount'] ?? 0);
            $vat      = (float) ($detail['vat'] ?? 0);
            $amount   = $qty * $price * (1 - $discount / 100) * (1 + $vat / 100);

            TransactionDetail::create([
                'transaction_id' => $transaction->id,
                'product_id'     => $detail['product_id'],
                'qty'            => $qty,
                'price'          => $price,
                'discount'       => $discount,
                'vat'            => $vat,
                'amount'         => $amount,
            ]);
        }

        // Upload ảnh đính kèm
        foreach ($request->file('images', []) as $file) {
            $path = $file->store('transactions/' . $transaction->id, 'public');
            TransactionAttachment::create([
                'transaction_id' => $transaction->id,
                'path'           => $path,
                'original_name'  => $file->getClientOriginalName(),
                'size'           => $file->getSize(),
                'mime_type'      => $file->getMimeType(),
            ]);
        }

        activity()->performedOn($transaction)->log('created');

        if ($request->has('submit')) {
            $this->service->submit($transaction);
        }

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Phiếu đã được tạo.');
    }

    public function show(Transaction $transaction): View
    {
        $transaction->load(['details.product', 'supplier', 'destination', 'createdBy', 'approvedBy', 'attachments']);

        return view('transactions.show', compact('transaction'));
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete-transactions');

        if (! $transaction->isDraft()) {
            return back()->with('error', 'Chỉ có thể xóa phiếu nháp.');
        }

        $transaction->delete();
        activity()->performedOn($transaction)->log('deleted');

        return redirect()->route('transactions.index')->with('success', 'Đã xóa phiếu.');
    }

    public function submit(Transaction $transaction): RedirectResponse
    {
        try {
            $this->service->submit($transaction);
            return back()->with('success', 'Phiếu đã được gửi chờ duyệt.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function approve(Transaction $transaction): RedirectResponse
    {
        try {
            $this->service->approve($transaction);
            return back()->with('success', 'Phiếu đã được duyệt thành công.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, Transaction $transaction): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'max:500']]);

        try {
            $this->service->reject($transaction, $request->reason);
            return back()->with('success', 'Phiếu đã bị từ chối.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroyAttachment(TransactionAttachment $attachment): RedirectResponse
    {
        $transaction = $attachment->transaction;

        if (! $transaction->isDraft()) {
            return back()->with('error', 'Chỉ xóa được ảnh trên phiếu nháp.');
        }

        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();

        return back()->with('success', 'Đã xóa ảnh.');
    }

    public function print(Transaction $transaction): View
    {
        $transaction->load(['details.product', 'supplier', 'destination', 'createdBy', 'approvedBy']);

        return view('transactions.print', compact('transaction'));
    }
}
