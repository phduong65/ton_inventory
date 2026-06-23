<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = Activity::with('causer')
            ->when($request->causer_id, fn ($q) => $q->where('causer_id', $request->causer_id)->where('causer_type', User::class))
            ->when($request->description, fn ($q) => $q->where('description', $request->description))
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->latest();

        $logs  = $query->paginate(30)->withQueryString();
        $users = User::orderBy('name')->get();

        $actions = Activity::distinct()->pluck('description')->sort()->values();

        return view('activity-logs.index', compact('logs', 'users', 'actions'));
    }
}
