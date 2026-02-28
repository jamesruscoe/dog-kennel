<?php

namespace App\Models\Traits;

use App\Models\CompanyContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Applies a global company scope to any model that has a company_id column.
 *
 * The scope is only applied when a CompanyContext is bound in the DI container
 * (i.e., when inside a tenant route resolved by ResolveCompanyFromPath middleware).
 * Root domain and platform routes never bind CompanyContext, so queries there
 * are never restricted.
 */
trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        static::addGlobalScope('company', function (Builder $builder) {
            if (app()->bound(CompanyContext::class)) {
                $table = $builder->getModel()->getTable();
                $builder->where("{$table}.company_id", app(CompanyContext::class)->id);
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }
}
