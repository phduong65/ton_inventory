<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(Request $request): View
    {
        $query = Unit::withCount(['products', 'unitConversions'])
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name');

        $units = $query->paginate(30)->withQueryString();

        return view('units.index', compact('units'));
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        $this->authorize('create-units');

        $unit = Unit::create($request->validated());

        activity()->performedOn($unit)->log('created');

        return redirect()->route('units.index')->with('success', 'Đã thêm đơn vị tính "' . $unit->name . '".');
    }

    public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    {
        $this->authorize('edit-units');

        $before = $unit->toArray();
        $unit->update($request->validated());

        activity()
            ->performedOn($unit)
            ->withProperties(['before' => $before, 'after' => $unit->getChanges()])
            ->log('updated');

        return redirect()->route('units.index')->with('success', 'Đã cập nhật đơn vị tính.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $this->authorize('delete-units');

        if ($unit->products()->exists()) {
            return back()->with('error', 'Không thể xóa đơn vị đang được sử dụng bởi sản phẩm.');
        }

        if ($unit->unitConversions()->exists()) {
            return back()->with('error', 'Không thể xóa đơn vị đang có trong bảng quy đổi.');
        }

        $unit->delete();
        activity()->performedOn($unit)->log('deleted');

        return redirect()->route('units.index')->with('success', 'Đã xóa đơn vị tính.');
    }
}
