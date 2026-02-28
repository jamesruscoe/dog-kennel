<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Str;

class CompanySlugService
{
    /**
     * Generate a URL-safe slug from a company name.
     * Lowercase, hyphens only (no underscores), alphanumeric.
     */
    public function generate(string $name): string
    {
        return Str::slug($name, '-');
    }

    /**
     * Check if a slug is unique in the companies table.
     */
    public function isUnique(string $slug, ?int $excludeId = null): bool
    {
        return ! Company::query()
            ->where('slug', $slug)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }

    /**
     * Generate a unique slug, appending -2, -3, etc. if the base is taken.
     */
    public function generateUnique(string $name, ?int $excludeId = null): string
    {
        $base = $this->generate($name);
        $slug = $base;
        $counter = 2;

        while (! $this->isUnique($slug, $excludeId)) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
