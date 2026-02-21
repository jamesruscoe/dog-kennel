<?php

namespace App\Enums;

enum UserRole: string
{
    case Staff = 'staff';
    case Owner = 'owner';

    public function label(): string
    {
        return match($this) {
            self::Staff => 'Staff',
            self::Owner => 'Dog Owner',
        };
    }

    public function isStaff(): bool
    {
        return $this === self::Staff;
    }

    public function isOwner(): bool
    {
        return $this === self::Owner;
    }
}
