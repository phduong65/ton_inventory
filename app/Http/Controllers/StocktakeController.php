<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStocktakeRequest;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Product;
use App\Models\StockLedger;
use App\Models\Stocktake;
use App\Services\StocktakeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StocktakeController extends Controller
{
    public function __construct(private StocktakeService $service) {}

    public function index(Request $request)
    {
        $this->authorize('view-stocktakes');

        $query = Stocktake::with(['createdBy', 'category', 'destination'])
            ->withCount('details');

        if ($request->destination_id === '0') {
            $query->whereNull('destination_id');
        } elseif ($request->destination_id) {
            $query->where('destination_id', $request->destination_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $stocktakes   = $query->latest()->paginate(20)->withQueryString();
        $destinations = Destination::orderBy('name')->get();

        return view('stocktakes.index', compact('stocktakes', 'destinations'));
    }

    public function create(Request $request)
    {
        $this->authorize('create-stocktakes');

        $destinationId = $request->destination_id;
        $destination   = null;

        if ($destinationId) {
            $destination = Destination::findOrFail($destinationId);

            // System qty = cumulative OUT qty sent to this destination per product
            $systemQtys = StockLedger::where('type', 'OUT')
                ->whereHas('transaction', fn ($q) => $q->where('status', 'approved')
                    ->where('destination_id', $destinationId))
                ->select('product_id', DB::raw('SUM(ABS(qty)) as total_qty'))
                ->groupBy('product_id')
                ->pluck('total_qty', 'product_id');

            $products = Product::active()
                ->with(['category.parent.parent', 'unit'])
                ->whereIn('id', $systemQtys->isEmpty() ? [0] : $systemQtys->keys())
                ->orderBy('name')
                ->get()
                ->each(fn ($p) => $p->system_qty = (float) ($systemQtys->get($p->id, 0)));

            $rootCategories = collect();
        } else {
            $rootCategories = Category::roots()->with('children.children')->get();
            $products       = Product::active()
                ->with(['inventory', 'category.parent.parent', 'unit'])
                ->orderBy('name')
                ->get()
                ->each(fn ($p) => $p->system_qty = (float) ($p->inventory?->quantity ?? 0));
        }

        // Dữ liệu sản phẩm nhúng vào JS cho command palette chọn sản phẩm kiểm kê
        $productsData = $products->mapWithKeys(fn ($p) => [
            $p->id => [
                'name'      => $p->name,
                'sku'       => $p->sku ?? '',
                'category'  => $p->category?->name ?? '',
                'rootId'    => $p->category?->getRootId(),
                'unitName'  => $p->unit?->name ?? '',
                'systemQty' => $p->system_qty,
            ],
        ]);

        return view('stocktakes.create', compact('productsData', 'rootCategories', 'destination'));
    }

    public function store(StoreStocktakeRequest $request)
    {
        $this->authorize('create-stocktakes');

        $data = $request->validated();

        $filledDetails = array_values(array_filter(
            $data['details'],
            fn ($row) => isset($row['actual_qty']) && $row['actual_qty'] !== null && $row['actual_qty'] !== '',
        ));

        if (empty($filledDetails)) {
            return back()->withInput()->with('error', 'Vui lòng nhập số lượng thực tế cho ít nhất một sản phẩm.');
        }

        $stocktake = Stocktake::create([
            'code'           => Stocktake::generateCode(),
            'status'         => 'draft',
            'created_by'     => auth()->id(),
            'note'           => $data['note'] ?? null,
            'category_id'    => $data['category_id'] ?? null,
            'destination_id' => $data['destination_id'] ?? null,
        ]);

        foreach ($filledDetails as $row) {
            $stocktake->details()->create([
                'product_id' => $row['product_id'],
                'system_qty' => $row['system_qty'],
                'actual_qty' => $row['actual_qty'],
                'variance'   => $row['actual_qty'] - $row['system_qty'],
            ]);
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($stocktake)
            ->log('created');

        if ($request->has('submit')) {
            $this->service->submit($stocktake);
            return redirect()->route('stocktakes.show', $stocktake)->with('success', 'Phiếu kiểm kê đã gửi chờ duyệt.');
        }

        return redirect()->route('stocktakes.show', $stocktake)->with('success', 'Đã lưu phiếu kiểm kê nháp.');
    }

    public function show(Stocktake $stocktake)
    {
        $this->authorize('view-stocktakes');
        $stocktake->load(['details.product.unit', 'createdBy', 'approvedBy', 'category', 'destination']);
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
        return back()->with('success', 'Đã duyệt phiếu kiểm kê.');
    }

    public function reject(Request $request, Stocktake $stocktake)
    {
        $this->authorize('reject-stocktakes');

        $request->validate(['reason' => ['required', 'string', 'max:500']]);

        if (! $stocktake->isPending()) {
            return back()->with('error', 'Chỉ có thể từ chối phiếu đang chờ duyệt.');
        }

        $stocktake->update([
            'status'          => 'rejected',
            'rejected_reason' => $request->reason,
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($stocktake)
            ->withProperties(['reason' => $request->reason])
            ->log('rejected');

        return back()->with('success', 'Phiếu kiểm kê đã bị từ chối.');
    }

    public function print(Stocktake $stocktake)
    {
        $this->authorize('view-stocktakes');
        $stocktake->load(['details.product.unit', 'createdBy', 'approvedBy', 'category', 'destination']);
        $companyName    = \App\Models\Setting::get('company_name', 'CÔNG TY F&B');
        $companyAddress = \App\Models\Setting::get('company_address', '');
        return view('stocktakes.print', compact('stocktake', 'companyName', 'companyAddress'));
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
