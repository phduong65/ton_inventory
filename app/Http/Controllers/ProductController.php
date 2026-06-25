<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Imports\ProductsImport;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Unit;
use App\Models\UnitConversion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with(['category', 'unit', 'inventory', 'unitConversions.unit'])
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%")
                  ->orWhere('barcode', 'like', "%{$request->search}%");
            }))
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('name');

        $products   = $query->paginate(20)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $units      = Unit::orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'units'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->authorize('create-products');

        $data = $request->validated();
        $conversions = $data['conversions'] ?? [];
        unset($data['conversions']);

        $product = Product::create($data);

        Inventory::create(['product_id' => $product->id, 'quantity' => 0, 'average_cost' => 0]);

        $this->syncConversions($product, $conversions);

        activity()->performedOn($product)->log('created');

        return redirect()->route('products.index')->with('success', 'Đã thêm sản phẩm.');
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('edit-products');

        $data = $request->validated();
        $conversions = $data['conversions'] ?? [];
        unset($data['conversions']);

        $before = $product->toArray();
        $product->update($data);

        $this->syncConversions($product, $conversions);

        activity()
            ->performedOn($product)
            ->withProperties(['before' => $before, 'after' => $product->getChanges()])
            ->log('updated');

        return redirect()->route('products.index')->with('success', 'Đã cập nhật sản phẩm.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete-products');

        if ($product->transactionDetails()->exists()) {
            return back()->with('error', 'Không thể xóa sản phẩm đã có phiếu nhập/xuất.');
        }

        $product->delete();
        activity()->performedOn($product)->log('deleted');

        return redirect()->route('products.index')->with('success', 'Đã xóa sản phẩm.');
    }

    private function syncConversions(Product $product, array $conversions): void
    {
        // Xóa conversions cũ rồi tạo lại (đơn giản, safe với fresh data)
        $product->unitConversions()->delete();

        foreach ($conversions as $conv) {
            if (empty($conv['unit_id']) || empty($conv['factor'])) {
                continue;
            }
            // Không cho phép quy đổi với chính đơn vị cơ sở
            if ((int) $conv['unit_id'] === (int) $product->unit_id) {
                continue;
            }
            UnitConversion::create([
                'product_id' => $product->id,
                'unit_id'    => $conv['unit_id'],
                'factor'     => $conv['factor'],
                'note'       => $conv['note'] ?? null,
            ]);
        }
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);

        $import = new ProductsImport();
        Excel::import($import, $request->file('file'));

        $msg = "Đã import {$import->imported} sản phẩm.";
        if ($import->skipped > 0) {
            $msg .= " Bỏ qua {$import->skipped} dòng (trùng SKU hoặc thiếu tên).";
        }

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['imported' => $import->imported, 'skipped' => $import->skipped])
            ->log('imported');

        return redirect()->route('products.index')->with('success', $msg);
    }
}
