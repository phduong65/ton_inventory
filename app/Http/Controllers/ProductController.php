<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with(['category', 'inventory'])
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

        return view('products.index', compact('products', 'categories'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $product = Product::create($data);

        // Tạo inventory record
        Inventory::create(['product_id' => $product->id, 'quantity' => 0, 'average_cost' => 0]);

        activity()->performedOn($product)->log('created');

        return redirect()->route('products.index')->with('success', 'Đã thêm sản phẩm.');
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        $before = $product->toArray();
        $product->update($data);

        activity()
            ->performedOn($product)
            ->withProperties(['before' => $before, 'after' => $product->getChanges()])
            ->log('updated');

        return redirect()->route('products.index')->with('success', 'Đã cập nhật sản phẩm.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->transactionDetails()->exists()) {
            return back()->with('error', 'Không thể xóa sản phẩm đã có phiếu nhập/xuất.');
        }

        $product->delete();
        activity()->performedOn($product)->log('deleted');

        return redirect()->route('products.index')->with('success', 'Đã xóa sản phẩm.');
    }
}
