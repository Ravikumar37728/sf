<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    // public function register()
    // {
    //     Collection::macro('paginate', function ($perPage, $total = null, $page = null, $pageName = 'page') {
    //         $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);
    //         return new LengthAwarePaginator(
    //             $this->forPage($page, $perPage),
    //             $total ?: $this->count(),
    //             $perPage,
    //             $page,
    //             [
    //                 'path' => LengthAwarePaginator::resolveCurrentPath(),
    //                 'pageName' => $pageName,
    //             ]
    //         );
    //     });
    // }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Builder::macro('whereLike', function ($attributes, string $searchTerm, string $baseClass) {
            $this->where(function (Builder $query) use ($attributes, $searchTerm, $baseClass) {
                foreach (Arr::wrap($attributes) as $attribute) {
                    $query->when(
                        str_contains($attribute, '.'),
                        function (Builder $query) use ($attribute, $searchTerm) {
                            $buffer = explode('.', $attribute);
                            $attributeField = array_pop($buffer);
                            $relationPath = implode('.', $buffer);
                            $query->orWhereHas($relationPath, function (Builder $query) use ($attributeField, $searchTerm, $relationPath) {
                                $query->where($attributeField, 'LIKE', "%{$searchTerm}%");
                                // if user() belongsTo relationship exists in any model
                                if ($relationPath == 'user') {
                                    $query->orWhereRaw("CONCAT(users.first_name, ' ', users.last_name) LIKE '%{$searchTerm}%' ");
                                }
                            });
                        },
                        function (Builder $query) use ($attribute, $searchTerm) {
                            $query->orWhere($attribute, 'LIKE', "%{$searchTerm}%");
                        }
                    );
                }
                // for User model full_name search
                if ($baseClass == "User") {
                    $query->orWhereRaw("CONCAT(users.first_name, ' ', users.last_name) LIKE '%{$searchTerm}%' ");
                }
            });
            return $this;
        });
    }
}
