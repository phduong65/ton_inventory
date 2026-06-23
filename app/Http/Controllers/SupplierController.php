<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $suppliers = Supplier::when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            }))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Supplier::create($data);
        activity()->log('created');

        return redirect()->route('suppliers.index')->with('success', 'Đã thêm nhà cung cấp.');
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $data = $request->validated();

        $supplier->update($data);
        activity()->performedOn($supplier)->log('updated');

        return redirect()->route('suppliers.index')->with('success', 'Đã cập nhật nhà cung cấp.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->transactions()->exists()) {
            return back()->with('error', 'Không thể xóa nhà cung cấp đã có phiếu nhập.');
        }

        $supplier->delete();
        activity()->performedOn($supplier)->log('deleted');

        return redirect()->route('suppliers.index')->with('success', 'Đã xóa nhà cung cấp.');
    }
}
