<?php

namespace App\Enums;

enum UserRole
{
    case Admin = "Admin";
    case Karyawan = "Karyawan";

    public static function values(): array
    {
        return array_map(fn($role) => $role->value, self::cases());
    }

}
