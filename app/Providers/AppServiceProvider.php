<?php

namespace App\Providers;

use App\Models\User;
use App\Support\StrutturaAccess;
use App\Support\StrutturaCorrente;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        View::composer('layouts.topbar', function ($view): void {
            $user = Auth::user();

            $payload = [
                'topbarStrutturaAllowed' => collect(),
                'topbarStrutturaCurrentId' => null,
                'topbarStrutturaCurrentName' => null,
                'topbarStrutturaState' => 'none',
                'topbarStrutturaCanSwitch' => false,
                'topbarStrutturaLegacyOutsideAllowed' => false,
            ];

            if (!$user instanceof User) {
                $view->with($payload);
                return;
            }

            $currentId = StrutturaCorrente::getId();
            $allowed = StrutturaAccess::query($user)
                ->select(['id', 'nome_struttura'])
                ->orderBy('nome_struttura')
                ->get();

            $current = $currentId !== null
                ? $allowed->firstWhere('id', (int) $currentId)
                : null;

            $legacyOutsideAllowed = false;
            $currentName = $current?->nome_struttura;

            if ($currentId !== null && !$current) {
                // Never widen selector scope: this lookup is only for displaying current operational context.
                $operationalCurrent = StrutturaAccess::query($user, true)
                    ->select(['id', 'nome_struttura'])
                    ->whereKey((int) $currentId)
                    ->first();

                if ($operationalCurrent) {
                    $legacyOutsideAllowed = true;
                    $currentName = $operationalCurrent->nome_struttura;
                }
            }

            if (!$currentName && $allowed->count() === 1) {
                $currentName = $allowed->first()->nome_struttura;
            }

            $state = 'none';
            if ($allowed->isNotEmpty()) {
                $state = $allowed->count() > 1 ? 'multi' : 'single';
            }

            $view->with(array_merge($payload, [
                'topbarStrutturaAllowed' => $allowed,
                'topbarStrutturaCurrentId' => $current?->id ?? ($currentId !== null ? (int) $currentId : null),
                'topbarStrutturaCurrentName' => $currentName,
                'topbarStrutturaState' => $state,
                'topbarStrutturaCanSwitch' => $state === 'multi' && !($user->isStrutturaUser() ?? false),
                'topbarStrutturaLegacyOutsideAllowed' => $legacyOutsideAllowed,
            ]));
        });

    }
}
