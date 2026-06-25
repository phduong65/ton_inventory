<?php

namespace App\View\Composers;

use App\Models\Transaction;
use Illuminate\View\View;

class GlobalDataComposer
{
    public function compose(View $view): void
    {
        if (auth()->check()) {
            if (!isset($view->getData()['pendingCount'])) {
                $view->with('pendingCount', Transaction::where('status', 'pending')->count());
            }
        }
    }
}
