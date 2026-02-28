<?php

namespace App\Models;

/**
 * Marker class used as a DI container binding key for the current tenant company.
 *
 * Usage:
 *   app()->instance(CompanyContext::class, $company)  // bind in middleware
 *   app(CompanyContext::class)                         // resolve anywhere
 *
 * This is simply a type alias — the actual bound value is a Company model.
 */
class CompanyContext extends Company {}
