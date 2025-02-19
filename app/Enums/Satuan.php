<?php

namespace App\Enums;

enum Satuan
{
    case Pcs = "Pcs";
    case Box = "Box";

    public static function values(): array
    {
        return array_map(fn($satuan) => $satuan->value, self::cases());
    }
}