<?php

namespace App\Model;

interface UserProxyIntertace
{
    // Administration système
    public const string PERSON_SUPER_ADMIN = 'SPADM'; // super administrateur plateforme
    public const string PERSON_ADMIN = 'ADM';         // administrateur système

    // Ressources humaines
    public const string PERSON_HR_ADMIN = 'HRADM';    // DRH / administrateur RH
    public const string PERSON_HR_STAFF = 'HRSTF';    // équipe RH siège

    // Direction
    public const string PERSON_EXECUTIVE = 'EXEC';    // DG / DGA / Directeur

    // Management
    public const string PERSON_MANAGER = 'MGR';       // responsable département / N+1

    // RH province
    public const string PERSON_HR_PROVINCE = 'HRPRV'; // RH en province

    // Employés
    public const string PERSON_EMPLOYEE = 'EMP';      // employé standard

    // Contractuels
    public const string PERSON_CONSULTANT = 'CNS';    // consultant externe
    public const string PERSON_INTERN = 'INT';        // stagiaire

    // Accès candidat (module recrutement)
    public const string PERSON_CANDIDATE = 'CND';     // candidat recrutement
}