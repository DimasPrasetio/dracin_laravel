<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryVipPackage;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class VipPackageController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('manageCategories', User::class);

        $categories = Category::orderBy('name')->get();
        $selectedCategoryId = (int) ($request->input('category_id') ?? $categories->first()?->id ?? 0);

        $packages = $selectedCategoryId
            ? CategoryVipPackage::where('category_id', $selectedCategoryId)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
            : collect();

        return view('admin.vip-packages.index', compact('categories', 'selectedCategoryId', 'packages'));
    }

    public function create(Request $request)
    {
        $this->authorize('manageCategories', User::class);

        $categories = Category::orderBy('name')->get();
        $selectedCategoryId = (int) ($request->input('category_id') ?? $categories->first()?->id ?? 0);

        return view('admin.vip-packages.create', compact('categories', 'selectedCategoryId'));
    }

    public function store(Request $request)
    {
        $this->authorize('manageCategories', User::class);

        $validator = Validator::make($request->all(), [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('category_vip_packages', 'code')->where('category_id', $request->input('category_id')),
            ],
            'name' => ['required', 'string', 'max:100'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'price' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
            'badge' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        CategoryVipPackage::create($data);

        return redirect()
            ->route('vip-packages.index', ['category_id' => $data['category_id']])
            ->with('success', 'Paket VIP berhasil ditambahkan.');
    }

    public function edit(CategoryVipPackage $vipPackage)
    {
        $this->authorize('manageCategories', User::class);

        $categories = Category::orderBy('name')->get();

        return view('admin.vip-packages.edit', compact('vipPackage', 'categories'));
    }

    public function update(Request $request, CategoryVipPackage $vipPackage)
    {
        $this->authorize('manageCategories', User::class);

        $validator = Validator::make($request->all(), [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('category_vip_packages', 'code')
                    ->where('category_id', $request->input('category_id'))
                    ->ignore($vipPackage->id),
            ],
            'name' => ['required', 'string', 'max:100'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'price' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
            'badge' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        $vipPackage->update($data);

        return redirect()
            ->route('vip-packages.index', ['category_id' => $data['category_id']])
            ->with('success', 'Paket VIP berhasil diperbarui.');
    }

    public function destroy(CategoryVipPackage $vipPackage)
    {
        $this->authorize('manageCategories', User::class);

        $hasPayments = Payment::where('category_id', $vipPackage->category_id)
            ->where('package', $vipPackage->code)
            ->exists();

        if ($hasPayments) {
            return redirect()->back()
                ->with('error', 'Paket tidak dapat dihapus karena sudah digunakan pada transaksi.');
        }

        $vipPackage->delete();

        return redirect()
            ->route('vip-packages.index', ['category_id' => $vipPackage->category_id])
            ->with('success', 'Paket VIP berhasil dihapus.');
    }
}
