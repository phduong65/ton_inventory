<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStocktakeRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stocktake;
use App\Services\StocktakeService;
use Illuminate\Http\Request;

class StocktakeController extends Controller
{
    public function __construct(private StocktakeService $service) {}

    public function index()
    {
        $this->authorize('view-stocktakes');
        $stocktakes = Stocktake::with(['createdBy', 'category'])->latest()->paginate(20);
        return view('stocktakes.index', compact('stocktakes'));
    }

    public function create()
    {
        $this->authorize('create-stocktakes');
        $rootCategories = Category::roots()->with('children.children')->get();
        $products = Product::active()->with(['inventory', 'category'])->orderBy('name')->get();
        return view('stocktakes.create', compact('products', 'rootCategories'));
    }

    public function store(StoreStocktakeRequest $request)
    {
        $this->authorize('create-stocktakes');

        $data = $request->validated();

        $stocktake = Stocktake::create([
            'code'        => Stocktake::generateCode(),
            'status'      => 'draft',
            'created_by'  => auth()->id(),
            'note'        => $data['note'] ?? null,
            'category_id' => $data['category_id'] ?? null,
        ]);

        foreach ($data['details'] as $row) {
            $stocktake->details()->create([
                'product_id' => $row['product_id'],
                'system_qty' => $row['system_qty'],
                'actual_qty' => $row['actual_qty'],
                'variance'   => $row['actual_qty'] - $row['system_qty'],
            ]);
        }

        if ($request->has('submit')) {
            $this->service->submit($stocktake);
            return redirect()->route('stocktakes.show', $stocktake)->with('success', 'Phiếu kiểm kê đã gửi chờ duyệt.');
        }

        return redirect()->route('stocktakes.show', $stocktake)->with('success', 'Đã lưu phiếu kiểm kê nháp.');
    }

    public function show(Stocktake $stocktake)
    {
        $this->authorize('view-stocktakes');
        $stocktake->load(['details.product', 'createdBy', 'approvedBy', 'category']);
        return view('stocktakes.show', compact('stocktake'));
    }

    public function submit(Stocktake $stocktake)
    {
        $this->authorize('create-stocktakes');
        $this->service->submit($stocktake);
        return back()->with('success', 'Phiếu đã gửi chờ duyệt.');
    }

    public function approve(Stocktake $stocktake)
    {
        $this->authorize('approve-stocktakes');
        $this->service->approve($stocktake, auth()->id());
        return back()->with('success', 'Đã duyệt phiếu kiểm kê. Tồn kho đã được cập nhật.');
    }

    public function destroy(Stocktake $stocktake)
    {
        $this->authorize('create-stocktakes');

        if ($stocktake->status !== 'draft') {
            return back()->with('error', 'Chỉ có thể xóa phiếu kiểm kê ở trạng thái nháp.');
        }

        $stocktake->delete();
        activity()->performedOn($stocktake)->log('deleted');

        return redirect()->route('stocktakes.index')->with('success', 'Đã xóa phiếu kiểm kê.');
    }
}
