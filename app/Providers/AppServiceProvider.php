<?php

namespace App\Providers;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\Transaction;
use App\Observers\ActivityObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Transaction::observe(ActivityObserver::class);
        Payment::observe(ActivityObserver::class);
        Expense::observe(ActivityObserver::class);
    }
}