<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin      = 'admin';
    case Staff      = 'staff';
    case Owner      = 'owner';

    public function label(): string
    {
        return match($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin      => 'Admin',
            self::Staff      => 'Staff',
            self::Owner      => 'Dog Owner',
        };
    }

    public function isSuperAdmin(): bool
    {
        return $this === self::SuperAdmin;
    }

    public function isAdmin(): bool
    {
        return $this === self::Admin;
    }

    public function isStaff(): bool
    {
        return $this === self::Staff;
    }

    public function isOwner(): bool
    {
        return $this === self::Owner;
    }

    /** Staff or Admin — can access the staff portal */
    public function isStaffOrAdmin(): bool
    {
        return $this === self::Staff || $this === self::Admin;
    }

    /** Any role scoped to a specific company (not super_admin) */
    public function isTenantUser(): bool
    {
        return $this !== self::SuperAdmin;
    }
}
