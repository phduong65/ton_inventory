<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $this->authorize('manage-settings');

        $requireApproval = Setting::get('require_approval', true);
        $companyName     = Setting::get('company_name', '');
        $companyAddress  = Setting::get('company_address', '');
        $companyPhone    = Setting::get('company_phone', '');
        $companyTaxCode  = Setting::get('company_tax_code', '');

        return view('settings.index', compact(
            'requireApproval', 'companyName', 'companyAddress', 'companyPhone', 'companyTaxCode'
        ));
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize('manage-settings');

        $request->validate([
            'company_name'     => ['nullable', 'string', 'max:200'],
            'company_address'  => ['nullable', 'string', 'max:500'],
            'company_phone'    => ['nullable', 'string', 'max:50'],
            'company_tax_code' => ['nullable', 'string', 'max:50'],
        ]);

        Setting::set('require_approval', $request->boolean('require_approval'));
        Setting::set('company_name',     $request->company_name ?? '');
        Setting::set('company_address',  $request->company_address ?? '');
        Setting::set('company_phone',    $request->company_phone ?? '');
        Setting::set('company_tax_code', $request->company_tax_code ?? '');

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['updated_keys' => ['require_approval', 'company_name', 'company_address', 'company_phone', 'company_tax_code']])
            ->log('updated settings');

        return back()->with('success', 'Đã lưu cài đặt.');
    }
}
