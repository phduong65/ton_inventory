<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuickCreateController extends Controller
{
    public function storeProduct(Request $request): JsonResponse
    {
        $this->authorize('create-products');

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:200'],
            'sku'           => ['nullable', 'string', 'max:50', 'unique:products'],
            'unit_id'       => ['required', 'exists:units,id'],
            'category_id'   => ['nullable', 'exists:categories,id'],
            'default_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        if (empty($data['sku'])) {
            $data['sku'] = 'SP-' . strtoupper(substr(uniqid(), -6));
        }

        $product = Product::create([
            'name'          => $data['name'],
            'sku'           => $data['sku'],
            'unit_id'       => $data['unit_id'],
            'category_id'   => $data['category_id'] ?? null,
            'default_price' => $data['default_price'] ?? 0,
            'status'        => 'active',
        ]);

        Inventory::create(['product_id' => $product->id, 'quantity' => 0, 'average_cost' => 0]);

        activity()->causedBy(auth()->user())->performedOn($product)->log('created');

        $product->load(['category', 'unit']);

        return response()->json([
            'id'           => $product->id,
            'name'         => $product->name,
            'sku'          => $product->sku ?? '',
            'category'     => $product->category?->name ?? '',
            'baseUnitId'   => $product->unit_id,
            'baseUnitName' => $product->unit?->name ?? '',
            'defaultPrice' => (float) ($product->default_price ?? 0),
            'stock'        => 0,
            'conversions'  => [],
        ]);
    }

    public function storeSupplier(Request $request): JsonResponse
    {
        $this->authorize('create-suppliers');

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:200'],
            'code'  => ['nullable', 'string', 'max:20', 'unique:suppliers'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
        ]);

        if (empty($data['code'])) {
            $data['code'] = 'NCC-' . strtoupper(substr(uniqid(), -5));
        }

        $supplier = Supplier::create($data);

        activity()->causedBy(auth()->user())->performedOn($supplier)->log('created');

        return response()->json([
            'id'   => $supplier->id,
            'name' => $supplier->name,
            'code' => $supplier->code,
        ]);
    }
}
