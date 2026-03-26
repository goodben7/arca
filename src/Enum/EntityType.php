<?php

namespace App\Enum;

class EntityType
{
    // === ENTITÉS PRINCIPALES ===
    public const string USER = 'USER'; // Utilisateur du système
    public const string PROFILE = 'PROFILE'; // Profil utilisateur
    public const string EMPLOYEE = 'EMPLOYEE'; // Employé
    public const string ACTIVITY = 'ACTIVITY'; // Historique d'activité
    public const string SKILL = 'SKILL'; // Compétence
    public const string WORK_EXPERIENCE = 'WORK_EXPERIENCE'; // Expérience professionnelle
    public const string DOCUMENT = 'DOCUMENT'; // Document
    public const string DEPARTMENT = 'DEPARTMENT'; // Département
    public const string CONTRACT = 'CONTRACT'; // Contrat
    public const string LEAVE_REQUEST = 'LEAVE_REQUEST'; // Demande de congé
    public const string POSITION = 'POSITION'; // Poste
    public const string RECRUITMENT_REQUEST = 'RECRUITMENT_REQUEST'; // Demande de recrutement
    public const string JOB_OFFER = 'JOB_OFFER'; // Offre d'emploi
    public const string APPLICATION = 'APPLICATION'; // Candidature
    public const string TRAINING_REQUEST = 'TRAINING_REQUEST'; // Demande de formation
    public const string TRAINING_SESSION = 'TRAINING_SESSION'; // Session de formation
    public const string TRAINING_ENROLLMENT = 'TRAINING_ENROLLMENT'; // Inscription à une session de formation



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
                self::ACTIVITY,
                self::SKILL,
                self::WORK_EXPERIENCE,
                self::DOCUMENT,
                self::DEPARTMENT,
                self::CONTRACT,
                self::LEAVE_REQUEST,
                self::POSITION,
                self::RECRUITMENT_REQUEST,
                self::JOB_OFFER,
                self::APPLICATION,
                self::TRAINING_REQUEST,
                self::TRAINING_SESSION,
                self::TRAINING_ENROLLMENT,
            ]
        ];
    }
}
