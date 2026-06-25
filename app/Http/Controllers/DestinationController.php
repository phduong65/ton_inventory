<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DestinationController extends Controller
{
    public function index(Request $request): View
    {
        $destinations = Destination::when(
            $request->search,
            fn($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
                  ->orWhere('manager', 'like', "%{$request->search}%");
            })
        )
            ->withCount('transactions')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('destinations.index', compact('destinations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage-destinations');

        $data = $request->validate([
            'code'    => 'nullable|string|max:20|unique:destinations,code',
            'name'    => 'required|string|max:100|unique:destinations,name',
            'phone'   => 'nullable|string|max:20',
            'manager' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'note'    => 'nullable|string',
        ], [
            'code.unique' => 'Mã kho đã tồn tại.',
            'code.max'    => 'Mã kho không quá 20 ký tự.',
            'name.required' => 'Tên kho là bắt buộc.',
            'name.unique'   => 'Tên kho đã tồn tại.',
            'name.max'      => 'Tên kho không quá 100 ký tự.',
        ]);

        $destination = Destination::create($data);
        activity()->performedOn($destination)->log('created');

        return redirect()->route('destinations.index')->with('success', 'Đã thêm kho nhận hàng.');
    }

    public function update(Request $request, Destination $destination): RedirectResponse
    {
        $this->authorize('manage-destinations');

        $data = $request->validate([
            'code'    => 'nullable|string|max:20|unique:destinations,code,' . $destination->id,
            'name'    => 'required|string|max:100|unique:destinations,name,' . $destination->id,
            'phone'   => 'nullable|string|max:20',
            'manager' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'note'    => 'nullable|string',
        ], [
            'code.unique' => 'Mã kho đã tồn tại.',
            'name.required' => 'Tên kho là bắt buộc.',
            'name.unique'   => 'Tên kho đã tồn tại.',
        ]);

        $destination->update($data);
        activity()->performedOn($destination)->log('updated');

        return redirect()->route('destinations.index')->with('success', 'Đã cập nhật kho nhận hàng.');
    }

    public function destroy(Destination $destination): RedirectResponse
    {
        $this->authorize('manage-destinations');

        if ($destination->transactions()->exists()) {
            return back()->with('error', 'Không thể xóa kho đã có phiếu xuất kho liên quan.');
        }

        activity()->performedOn($destination)->log('deleted');
        $destination->delete();

        return redirect()->route('destinations.index')->with('success', 'Đã xóa kho nhận hàng.');
    }
}
