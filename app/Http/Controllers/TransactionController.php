<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\TransactionAttachment;
use App\Models\TransactionDetail;
use App\Models\Unit;
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
        $products     = Product::active()->with(['category', 'unit', 'inventory', 'unitConversions.unit'])->orderBy('name')->get();
        $categories   = Category::orderBy('name')->get();
        $units        = Unit::orderBy('name')->get();

        // Dữ liệu sản phẩm nhúng vào JS cho command palette + quy đổi đơn vị
        $productsUnitData = $products->mapWithKeys(fn($p) => [
            $p->id => [
                'name'         => $p->name,
                'sku'          => $p->sku ?? '',
                'category'     => $p->category?->name ?? '',
                'baseUnitId'   => $p->unit_id,
                'baseUnitName' => $p->unit?->name ?? '',
                'defaultPrice' => $p->default_price,
                'stock'        => (float) ($p->inventory?->quantity ?? 0),
                'conversions'  => $p->unitConversions->map(fn($c) => [
                    'unitId'   => $c->unit_id,
                    'unitName' => $c->unit?->name ?? '',
                    'factor'   => $c->factor,
                ])->values(),
            ],
        ]);

        return view('transactions.create', compact('type', 'suppliers', 'destinations', 'products', 'productsUnitData', 'categories', 'units'));
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
            $qty              = (float) $detail['qty'];
            $conversionFactor = (float) ($detail['conversion_factor'] ?? 1);
            $baseQty          = $qty * $conversionFactor;
            $price            = (float) ($detail['price'] ?? 0);
            $discount         = (float) ($detail['discount'] ?? 0);
            $vat              = (float) ($detail['vat'] ?? 0);
            $amount           = $qty * $price * (1 - $discount / 100) * (1 + $vat / 100);

            TransactionDetail::create([
                'transaction_id'    => $transaction->id,
                'product_id'        => $detail['product_id'],
                'unit_id'           => $detail['unit_id'],
                'conversion_factor' => $conversionFactor,
                'base_qty'          => $baseQty,
                'qty'               => $qty,
                'price'             => $price,
                'discount'          => $discount,
                'vat'               => $vat,
                'amount'            => $amount,
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
        $transaction->load(['details.product.unit', 'details.unit', 'supplier', 'destination', 'createdBy', 'approvedBy', 'attachments']);

        return view('transactions.show', compact('transaction'));
    }

    public function edit(Transaction $transaction): View|RedirectResponse
    {
        if (! $transaction->isDraft()) {
            return redirect()->route('transactions.show', $transaction)
                ->with('error', 'Chỉ có thể sửa phiếu đang ở trạng thái nháp.');
        }

        $transaction->load(['details.product', 'details.unit', 'attachments']);
        $type         = $transaction->type;
        $suppliers    = Supplier::orderBy('name')->get();
        $destinations = Destination::all();
        $products     = Product::active()->with(['category', 'unit', 'inventory', 'unitConversions.unit'])->orderBy('name')->get();
        $categories   = Category::orderBy('name')->get();
        $units        = Unit::orderBy('name')->get();

        $productsUnitData = $products->mapWithKeys(fn ($p) => [
            $p->id => [
                'name'         => $p->name,
                'sku'          => $p->sku ?? '',
                'category'     => $p->category?->name ?? '',
                'baseUnitId'   => $p->unit_id,
                'baseUnitName' => $p->unit?->name ?? '',
                'defaultPrice' => $p->default_price,
                'stock'        => (float) ($p->inventory?->quantity ?? 0),
                'conversions'  => $p->unitConversions->map(fn ($c) => [
                    'unitId'   => $c->unit_id,
                    'unitName' => $c->unit?->name ?? '',
                    'factor'   => $c->factor,
                ])->values(),
            ],
        ]);

        return view('transactions.edit', compact('transaction', 'type', 'suppliers', 'destinations', 'products', 'productsUnitData', 'categories', 'units'));
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('edit-transactions');

        if (! $transaction->isDraft()) {
            return back()->with('error', 'Chỉ có thể sửa phiếu đang ở trạng thái nháp.');
        }

        $request->validate([
            'date'                 => ['required', 'date'],
            'supplier_id'          => ['nullable', 'exists:suppliers,id'],
            'destination_id'       => ['nullable', 'exists:destinations,id'],
            'note'                 => ['nullable', 'string', 'max:1000'],
            'details'              => ['required', 'array', 'min:1'],
            'details.*.product_id' => ['required', 'exists:products,id'],
            'details.*.qty'        => ['required', 'numeric', 'min:0.001'],
            'details.*.price'      => ['nullable', 'numeric', 'min:0'],
            'details.*.discount'   => ['nullable', 'numeric', 'min:0', 'max:100'],
            'details.*.vat'        => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $transaction->update([
            'supplier_id'    => $request->supplier_id,
            'destination_id' => $request->destination_id,
            'date'           => $request->date,
            'note'           => $request->note,
        ]);

        $transaction->details()->delete();

        foreach ($request->details as $detail) {
            $qty              = (float) $detail['qty'];
            $conversionFactor = (float) ($detail['conversion_factor'] ?? 1);
            $baseQty          = $qty * $conversionFactor;
            $price            = (float) ($detail['price'] ?? 0);
            $discount         = (float) ($detail['discount'] ?? 0);
            $vat              = (float) ($detail['vat'] ?? 0);
            $amount           = $qty * $price * (1 - $discount / 100) * (1 + $vat / 100);

            $transaction->details()->create([
                'product_id'        => $detail['product_id'],
                'unit_id'           => $detail['unit_id'] ?? null,
                'conversion_factor' => $conversionFactor,
                'base_qty'          => $baseQty,
                'qty'               => $qty,
                'price'             => $price,
                'discount'          => $discount,
                'vat'               => $vat,
                'amount'            => $amount,
            ]);
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($transaction)
            ->withProperties(['action' => 'updated draft'])
            ->log('updated');

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Phiếu đã được cập nhật.');
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

    public function cancel(Transaction $transaction): RedirectResponse
    {
        if (! $transaction->isPending()) {
            return back()->with('error', 'Chỉ có thể hủy phiếu đang chờ duyệt.');
        }

        if (! auth()->user()->hasRole('admin') && $transaction->created_by !== auth()->id()) {
            return back()->with('error', 'Bạn không có quyền hủy phiếu này.');
        }

        $transaction->update(['status' => 'draft']);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($transaction)
            ->log('cancelled');

        return back()->with('success', 'Phiếu đã được hủy và chuyển về trạng thái nháp.');
    }

    public function clone(Transaction $transaction): RedirectResponse
    {
        $this->authorize('create-transactions');

        $newTransaction = Transaction::create([
            'code'           => Transaction::generateCode($transaction->type),
            'type'           => $transaction->type,
            'status'         => 'draft',
            'supplier_id'    => $transaction->supplier_id,
            'destination_id' => $transaction->destination_id,
            'created_by'     => auth()->id(),
            'date'           => today()->format('Y-m-d'),
            'note'           => $transaction->note,
        ]);

        foreach ($transaction->details as $detail) {
            $newTransaction->details()->create([
                'product_id'        => $detail->product_id,
                'unit_id'           => $detail->unit_id,
                'conversion_factor' => $detail->conversion_factor,
                'base_qty'          => $detail->base_qty,
                'qty'               => $detail->qty,
                'price'             => $detail->price,
                'discount'          => $detail->discount,
                'vat'               => $detail->vat,
                'amount'            => $detail->amount,
            ]);
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($newTransaction)
            ->withProperties(['cloned_from' => $transaction->code])
            ->log('created');

        return redirect()->route('transactions.show', $newTransaction)
            ->with('success', "Đã nhân bản từ phiếu {$transaction->code}.");
    }

    public function print(Transaction $transaction): View
    {
        $transaction->load(['details.product.unit', 'details.unit', 'supplier', 'destination', 'createdBy', 'approvedBy', 'attachments']);

        return view('transactions.print', compact('transaction'));
    }
}
