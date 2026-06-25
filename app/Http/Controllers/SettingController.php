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

        return view('settings.index', compact('requireApproval'));
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize('manage-settings');

        Setting::set('require_approval', $request->boolean('require_approval'));

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['require_approval' => $request->boolean('require_approval')])
            ->log('updated settings');

        return back()->with('success', 'Đã lưu cài đặt.');
    }
}
