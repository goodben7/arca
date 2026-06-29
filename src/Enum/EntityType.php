<?php

namespace App\Enum;

class EntityType
{
    // === ENTITÉS PRINCIPALES ===
    public const string USER = 'USER'; // Utilisateur du système
    public const string PROFILE = 'PROFILE'; // Profil utilisateur
    public const string EMPLOYEE = 'EMPLOYEE'; // Employé
    public const string EMPLOYEE_JOURNEY_ENTRY = 'EMPLOYEE_JOURNEY_ENTRY'; // Entrée du parcours employé
    public const string ACTIVITY = 'ACTIVITY'; // Historique d'activité
    public const string SKILL = 'SKILL'; // Compétence catalogue
    public const string SKILL_CATEGORY = 'SKILL_CATEGORY'; // Catégorie de compétences
    public const string EMPLOYEE_SKILL = 'EMPLOYEE_SKILL'; // Compétence détenue par un employé
    public const string JOB_ROLE_REQUIRED_SKILL = 'JOB_ROLE_REQUIRED_SKILL'; // Compétence requise pour une fiche métier
    public const string WORK_EXPERIENCE = 'WORK_EXPERIENCE'; // Expérience professionnelle
    public const string DOCUMENT = 'DOCUMENT'; // Document
    public const string DEPARTMENT = 'DEPARTMENT'; // Département
    public const string JOB_FAMILY = 'JOB_FAMILY'; // Famille de métiers
    public const string GRADE = 'GRADE'; // Grade
    public const string JOB_ROLE = 'JOB_ROLE'; // Fiche métier
    public const string CAREER_PATH = 'CAREER_PATH'; // Parcours de carrière
    public const string CONTRACT = 'CONTRACT'; // Contrat
    public const string LEAVE_REQUEST = 'LEAVE_REQUEST'; // Demande de congé
    public const string POSITION = 'POSITION'; // Poste
    public const string RECRUITMENT_REQUEST = 'RECRUITMENT_REQUEST'; // Demande de recrutement
    public const string JOB_OFFER = 'JOB_OFFER'; // Offre d'emploi
    public const string APPLICATION = 'APPLICATION'; // Candidature
    public const string TRAINING_REQUEST = 'TRAINING_REQUEST'; // Demande de formation
    public const string TRAINING_SESSION = 'TRAINING_SESSION'; // Session de formation
    public const string TRAINING_ENROLLMENT = 'TRAINING_ENROLLMENT'; // Inscription à une session de formation
    public const string ONBOARDING_PROCESS = 'ONBOARDING_PROCESS'; // Processus d'onboarding
    public const string ONBOARDING_TASK = 'ONBOARDING_TASK'; // Tâche d'onboarding
    public const string EVALUATION_CYCLE = 'EVALUATION_CYCLE'; // Cycle d'évaluation
    public const string PERFORMANCE_REVIEW = 'PERFORMANCE_REVIEW'; // Évaluation de performance
    public const string OBJECTIVE = 'OBJECTIVE'; // Objectif SMART
    public const string TRAINING_CATALOG = 'TRAINING_CATALOG'; // Catalogue de formations
    public const string JOB_ROLE_REQUIRED_TRAINING = 'JOB_ROLE_REQUIRED_TRAINING'; // Formation requise pour une fiche métier
    public const string CAREER_PLAN = 'CAREER_PLAN'; // Plan de carrière employé
    public const string MOBILITY_REQUEST = 'MOBILITY_REQUEST'; // Demande de mobilité
    public const string COMPENSATION_HISTORY = 'COMPENSATION_HISTORY'; // Historique de rémunération
    public const string BENEFIT = 'BENEFIT'; // Avantage employeur
    public const string EMPLOYEE_BENEFIT = 'EMPLOYEE_BENEFIT'; // Avantage attribué à un employé
    public const string EXIT_PROCESS = 'EXIT_PROCESS'; // Processus de sortie
    public const string EXIT_TASK = 'EXIT_TASK'; // Tâche de sortie
    public const string SUCCESSION_PLAN = 'SUCCESSION_PLAN'; // Plan de succession



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
                self::EMPLOYEE_JOURNEY_ENTRY,
                self::ACTIVITY,
                self::SKILL,
                self::SKILL_CATEGORY,
                self::EMPLOYEE_SKILL,
                self::JOB_ROLE_REQUIRED_SKILL,
                self::WORK_EXPERIENCE,
                self::DOCUMENT,
                self::DEPARTMENT,
                self::JOB_FAMILY,
                self::GRADE,
                self::JOB_ROLE,
                self::CAREER_PATH,
                self::CONTRACT,
                self::LEAVE_REQUEST,
                self::POSITION,
                self::RECRUITMENT_REQUEST,
                self::JOB_OFFER,
                self::APPLICATION,
                self::TRAINING_REQUEST,
                self::TRAINING_SESSION,
                self::TRAINING_ENROLLMENT,
                self::ONBOARDING_PROCESS,
                self::ONBOARDING_TASK,
                self::EVALUATION_CYCLE,
                self::PERFORMANCE_REVIEW,
                self::OBJECTIVE,
                self::TRAINING_CATALOG,
                self::JOB_ROLE_REQUIRED_TRAINING,
                self::CAREER_PLAN,
                self::MOBILITY_REQUEST,
                self::COMPENSATION_HISTORY,
                self::BENEFIT,
                self::EMPLOYEE_BENEFIT,
                self::EXIT_PROCESS,
                self::EXIT_TASK,
                self::SUCCESSION_PLAN,
            ]
        ];
    }
}
