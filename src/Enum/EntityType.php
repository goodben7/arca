<?php

namespace App\Enum;

class EntityType
{
    // === ENTITÉS PRINCIPALES ===
    public const string USER = 'USER'; // Utilisateur du système
    public const string PROFILE = 'PROFILE'; // Profil utilisateur
    public const string EMPLOYEE = 'EMPLOYEE'; // Employé
    public const string SKILL = 'SKILL'; // Compétence
    public const string WORK_EXPERIENCE = 'WORK_EXPERIENCE'; // Expérience professionnelle
    public const string DOCUMENT = 'DOCUMENT'; // Document
    public const string DEPARTMENT = 'DEPARTMENT'; // Département
    public const string CONTRACT = 'CONTRACT'; // Contrat



    public static function getAll(): array
    {
        $reflection = new \ReflectionClass(self::class);
        return $reflection->getConstants();
    }

    public static function getGrouped(): array
    {
        return [
            'entities' => [
                self::USER,
                self::PROFILE,
                self::EMPLOYEE,
                self::SKILL,
                self::WORK_EXPERIENCE,
                self::DOCUMENT,
                self::DEPARTMENT,
                self::CONTRACT,
            ]
        ];
    }
}
