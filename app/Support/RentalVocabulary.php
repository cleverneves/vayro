<?php

namespace App\Support;

class RentalVocabulary
{
    public const ROLES = [
        'renter' => 'locatario',
        'admin' => 'administrativo',
    ];

    public const REASONS = [
        'trip' => 'viagem',
        'leisure' => 'passeio',
        'everyday' => 'dia-a-dia',
    ];

    public const STATUSES = [
        'requested' => 'solicitada',
        'confirmed' => 'confirmada',
        'in_progress' => 'em_andamento',
        'completed' => 'concluida',
        'cancelled' => 'cancelada',
    ];

    public static function roleToPortuguese(string $role): ?string
    {
        return self::ROLES[$role] ?? null;
    }

    public static function roleToEnglish(string $papel): ?string
    {
        return array_flip(self::ROLES)[$papel] ?? null;
    }

    public static function reasonToPortuguese(string $reason): ?string
    {
        return self::REASONS[$reason] ?? null;
    }

    public static function reasonToEnglish(string $motivo): ?string
    {
        return array_flip(self::REASONS)[$motivo] ?? null;
    }

    public static function statusToPortuguese(string $status): ?string
    {
        return self::STATUSES[$status] ?? null;
    }

    public static function statusToEnglish(string $status): ?string
    {
        return array_flip(self::STATUSES)[$status] ?? null;
    }
}
