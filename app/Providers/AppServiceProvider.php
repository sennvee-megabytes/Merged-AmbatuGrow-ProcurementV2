<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $moduleViewPaths = [
            'grim' => resource_path('views/grim'),
            'orders' => resource_path('views'),
            'requisitions' => resource_path('views/requisitions'),
            'suppliers' => resource_path('views/suppliers'),
        ];

        foreach ($moduleViewPaths as $namespace => $path) {
            View::addNamespace($namespace, $path);
        }

        View::composer('*', function ($view) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                $userId = \Illuminate\Support\Facades\Auth::id();
                $pendingCount = \App\Models\Requisition::where('status', 'pending_approval')
                    ->whereHas('approvalSteps', function ($q) use ($userId) {
                        $q->where('approver_id', $userId)->where('status', 'pending');
                    })
                    ->get()
                    ->filter(function (\App\Models\Requisition $r) use ($userId) {
                        if ($r->approval_type === 'parallel') return true;
                        $current = $r->currentStep();
                        return $current && (int)$current->approver_id === (int)$userId;
                    })
                    ->count();

                $view->with('pendingApprovalCount', $pendingCount);
            } else {
                $view->with('pendingApprovalCount', 0);
            }
        });
    }
}
