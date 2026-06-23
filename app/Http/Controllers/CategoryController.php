<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::with('parent', 'children')
            ->withCount('products')
            ->orderBy('sort')
            ->get();

        $roots = Category::roots()->orderBy('name')->get();

        return view('categories.index', compact('categories', 'roots'));
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Category::create($data);

        activity()->log('created');

        return redirect()->route('categories.index')->with('success', 'Đã thêm ngành hàng.');
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();

        $category->update($data);

        activity()->performedOn($category)->log('updated');

        return redirect()->route('categories.index')->with('success', 'Đã cập nhật ngành hàng.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'Không thể xóa ngành hàng đã có sản phẩm.');
        }

        $category->delete();
        activity()->performedOn($category)->log('deleted');

        return redirect()->route('categories.index')->with('success', 'Đã xóa ngành hàng.');
    }
}
