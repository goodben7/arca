<?php

declare(strict_types=1);

use App\Model\Permission;

return static function (): iterable {

    yield Permission::new('ROLE_USER_CREATE', "Créér u n utilisateur");
    yield Permission::new('ROLE_USER_LOCK', "Vérouiller/Déverrouiller un utilisateur");
    yield Permission::new('ROLE_USER_CHANGE_PWD', "Modifier mot de passe");
    yield Permission::new('ROLE_USER_DETAILS', "Consulter les détails d'un utilisateur");
    yield Permission::new('ROLE_USER_LIST', "Consulter la liste des utilisateurs");
    yield Permission::new('ROLE_USER_EDIT', "Editer les informations d'un utilisateur");
    yield Permission::new('ROLE_USER_DELETE', "Supprimer un utilisateur");
    yield Permission::new('ROLE_USER_SET_PROFILE', "Modifier le profil utilisateur");

    yield Permission::new('ROLE_PROFILE_CREATE', "Créer un profil utilisateur");
    yield Permission::new('ROLE_PROFILE_LIST', "Consulter la liste des profils utilisateur");
    yield Permission::new('ROLE_PROFILE_UPDATE', "Modifier un profil utilisateur");
    yield Permission::new('ROLE_PROFILE_DETAILS', "Consulter les détails d'un profil utilisateur");

    yield Permission::new('ROLE_ACTIVITY_LIST', "Consulter la liste des activités"); 
    yield Permission::new('ROLE_ACTIVITY_VIEW', "Consulter les détails d'une activité"); 

    yield Permission::new('ROLE_EMPLOYEE_CREATE', "Créer un employé");
    yield Permission::new('ROLE_EMPLOYEE_LIST', "Consulter la liste des employés");
    yield Permission::new('ROLE_EMPLOYEE_DETAILS', "Consulter les détails d'un employé");
    yield Permission::new('ROLE_EMPLOYEE_UPDATE', "Editer les informations d'un employé");
    yield Permission::new('ROLE_EMPLOYEE_ACTIVATE', "Activer un employé");
    yield Permission::new('ROLE_EMPLOYEE_DEACTIVATE', "Désactiver un employé");
    yield Permission::new('ROLE_EMPLOYEE_SET_ON_LEAVE', "Mettre un employé en congé");
    yield Permission::new('ROLE_EMPLOYEE_SUSPEND', "Suspendre un employé");
    yield Permission::new('ROLE_EMPLOYEE_TERMINATE', "Mettre fin au contrat d'un employé");
    yield Permission::new('ROLE_EMPLOYEE_RETIRE', "Mettre un employé à la retraite");
    yield Permission::new('ROLE_EMPLOYEE_SET_PROBATION', "Mettre un employé en période d'essai");
    yield Permission::new('ROLE_EMPLOYEE_ASSIGN_MANAGER', "Assigner un manager à un employé");
    yield Permission::new('ROLE_EMPLOYEE_JOURNEY_LIST', "Consulter la timeline du parcours employé");

    yield Permission::new('ROLE_WORK_EXPERIENCE_CREATE', "Créer une expérience professionnelle");
    yield Permission::new('ROLE_WORK_EXPERIENCE_LIST', "Consulter la liste des expériences professionnelles");
    yield Permission::new('ROLE_WORK_EXPERIENCE_DETAILS', "Consulter les détails d'une expérience professionnelle");
    yield Permission::new('ROLE_WORK_EXPERIENCE_UPDATE', "Modifier une expérience professionnelle");

    yield Permission::new('ROLE_SKILL_CATEGORY_CREATE', "Créer une catégorie de compétences");
    yield Permission::new('ROLE_SKILL_CATEGORY_LIST', "Consulter la liste des catégories de compétences");
    yield Permission::new('ROLE_SKILL_CATEGORY_DETAILS', "Consulter les détails d'une catégorie de compétences");
    yield Permission::new('ROLE_SKILL_CATEGORY_UPDATE', "Modifier une catégorie de compétences");

    yield Permission::new('ROLE_SKILL_CREATE', "Créer une compétence du catalogue");
    yield Permission::new('ROLE_SKILL_LIST', "Consulter la liste des compétences du catalogue");
    yield Permission::new('ROLE_SKILL_DETAILS', "Consulter les détails d'une compétence du catalogue");
    yield Permission::new('ROLE_SKILL_UPDATE', "Modifier une compétence du catalogue");

    yield Permission::new('ROLE_EMPLOYEE_SKILL_CREATE', "Créer une compétence employé");
    yield Permission::new('ROLE_EMPLOYEE_SKILL_LIST', "Consulter la liste des compétences employé");
    yield Permission::new('ROLE_EMPLOYEE_SKILL_DETAILS', "Consulter les détails d'une compétence employé");
    yield Permission::new('ROLE_EMPLOYEE_SKILL_UPDATE', "Modifier une compétence employé");

    yield Permission::new('ROLE_JOB_ROLE_REQUIRED_SKILL_CREATE', "Créer une compétence requise pour une fiche métier");
    yield Permission::new('ROLE_JOB_ROLE_REQUIRED_SKILL_LIST', "Consulter les compétences requises par fiche métier");
    yield Permission::new('ROLE_JOB_ROLE_REQUIRED_SKILL_DETAILS', "Consulter le détail d'une compétence requise");
    yield Permission::new('ROLE_JOB_ROLE_REQUIRED_SKILL_UPDATE', "Modifier une compétence requise pour une fiche métier");

    yield Permission::new('ROLE_DOC_CREATE', "Créer un document");
    yield Permission::new('ROLE_DOC_LIST', "Consulter la liste des documents");
    yield Permission::new('ROLE_DOC_DETAILS', "Consulter les détails d'un document");
    yield Permission::new('ROLE_DOC_DELETE', "Supprimer un document");

    yield Permission::new('ROLE_DEPARTMENT_CREATE', "Créer un département");
    yield Permission::new('ROLE_DEPARTMENT_LIST', "Consulter la liste des départements");
    yield Permission::new('ROLE_DEPARTMENT_DETAILS', "Consulter les détails d'un département");
    yield Permission::new('ROLE_DEPARTMENT_UPDATE', "Modifier un département");

    yield Permission::new('ROLE_JOB_FAMILY_CREATE', "Créer une famille de métiers");
    yield Permission::new('ROLE_JOB_FAMILY_LIST', "Consulter la liste des familles de métiers");
    yield Permission::new('ROLE_JOB_FAMILY_DETAILS', "Consulter les détails d'une famille de métiers");
    yield Permission::new('ROLE_JOB_FAMILY_UPDATE', "Modifier une famille de métiers");

    yield Permission::new('ROLE_GRADE_CREATE', "Créer un grade");
    yield Permission::new('ROLE_GRADE_LIST', "Consulter la liste des grades");
    yield Permission::new('ROLE_GRADE_DETAILS', "Consulter les détails d'un grade");
    yield Permission::new('ROLE_GRADE_UPDATE', "Modifier un grade");

    yield Permission::new('ROLE_JOB_ROLE_CREATE', "Créer une fiche métier");
    yield Permission::new('ROLE_JOB_ROLE_LIST', "Consulter la liste des fiches métier");
    yield Permission::new('ROLE_JOB_ROLE_DETAILS', "Consulter les détails d'une fiche métier");
    yield Permission::new('ROLE_JOB_ROLE_UPDATE', "Modifier une fiche métier");

    yield Permission::new('ROLE_CAREER_PATH_CREATE', "Créer un parcours de carrière");
    yield Permission::new('ROLE_CAREER_PATH_LIST', "Consulter la liste des parcours de carrière");
    yield Permission::new('ROLE_CAREER_PATH_DETAILS', "Consulter les détails d'un parcours de carrière");
    yield Permission::new('ROLE_CAREER_PATH_UPDATE', "Modifier un parcours de carrière");

    yield Permission::new('ROLE_CONTRACT_CREATE', "Créer un contrat");
    yield Permission::new('ROLE_CONTRACT_LIST', "Consulter la liste des contrats");
    yield Permission::new('ROLE_CONTRACT_DETAILS', "Consulter les détails d'un contrat");
    yield Permission::new('ROLE_CONTRACT_UPDATE', "Modifier un contrat");
    yield Permission::new('ROLE_CONTRACT_ACTIVATE', "Activer un contrat");
    yield Permission::new('ROLE_CONTRACT_END', "Clôturer un contrat");
    yield Permission::new('ROLE_CONTRACT_CANCEL', "Annuler un contrat");
    yield Permission::new('ROLE_CONTRACT_SET_PENDING', "Remettre un contrat en attente");

    yield Permission::new('ROLE_LEAVE_REQUEST_CREATE', "Créer une demande de congé");
    yield Permission::new('ROLE_LEAVE_REQUEST_LIST', "Consulter la liste des demandes de congé");
    yield Permission::new('ROLE_LEAVE_REQUEST_DETAILS', "Consulter les détails d'une demande de congé");
    yield Permission::new('ROLE_LEAVE_REQUEST_UPDATE', "Modifier une demande de congé");
    yield Permission::new('ROLE_LEAVE_REQUEST_APPROVE', "Approuver une demande de congé");
    yield Permission::new('ROLE_LEAVE_REQUEST_REJECT', "Rejeter une demande de congé");

    yield Permission::new('ROLE_POSITION_CREATE', "Créer un poste");
    yield Permission::new('ROLE_POSITION_LIST', "Consulter la liste des postes");
    yield Permission::new('ROLE_POSITION_DETAILS', "Consulter les détails d'un poste");
    yield Permission::new('ROLE_POSITION_UPDATE', "Modifier un poste");
    yield Permission::new('ROLE_POSITION_OPEN', "Ouvrir un poste");
    yield Permission::new('ROLE_POSITION_CLOSE', "Fermer un poste");

    yield Permission::new('ROLE_RECRUITMENT_REQUEST_CREATE', "Créer une demande de recrutement");
    yield Permission::new('ROLE_RECRUITMENT_REQUEST_LIST', "Consulter la liste des demandes de recrutement");
    yield Permission::new('ROLE_RECRUITMENT_REQUEST_DETAILS', "Consulter les détails d'une demande de recrutement");
    yield Permission::new('ROLE_RECRUITMENT_REQUEST_APPROVE', "Approuver une demande de recrutement");
    yield Permission::new('ROLE_RECRUITMENT_REQUEST_REJECT', "Rejeter une demande de recrutement");

    yield Permission::new('ROLE_JOB_OFFER_CREATE', "Créer une offre d'emploi");
    yield Permission::new('ROLE_JOB_OFFER_LIST', "Consulter la liste des offres d'emploi");
    yield Permission::new('ROLE_JOB_OFFER_DETAILS', "Consulter les détails d'une offre d'emploi");
    yield Permission::new('ROLE_JOB_OFFER_UPDATE', "Modifier une offre d'emploi");
    yield Permission::new('ROLE_JOB_OFFER_PUBLISH', "Publier une offre d'emploi");
    yield Permission::new('ROLE_JOB_OFFER_CLOSE', "Fermer une offre d'emploi");
    yield Permission::new('ROLE_JOB_OFFER_SET_DRAFT', "Remettre une offre d'emploi en brouillon");

    yield Permission::new('ROLE_APPLICATION_CREATE', "Créer une candidature");
    yield Permission::new('ROLE_APPLICATION_LIST', "Consulter la liste des candidatures");
    yield Permission::new('ROLE_APPLICATION_DETAILS', "Consulter les détails d'une candidature");
    yield Permission::new('ROLE_APPLICATION_SET_APPLIED', "Remettre une candidature à l'état APPLIED");
    yield Permission::new('ROLE_APPLICATION_SHORTLIST', "Shortlister une candidature");
    yield Permission::new('ROLE_APPLICATION_INTERVIEW', "Mettre une candidature en entretien");
    yield Permission::new('ROLE_APPLICATION_REJECT', "Rejeter une candidature");
    yield Permission::new('ROLE_APPLICATION_HIRE', "Embaucher une candidature");

    yield Permission::new('ROLE_TRAINING_REQUEST_CREATE', "Créer une demande de formation");
    yield Permission::new('ROLE_TRAINING_REQUEST_LIST', "Consulter la liste des demandes de formation");
    yield Permission::new('ROLE_TRAINING_REQUEST_DETAILS', "Consulter les détails d'une demande de formation");
    yield Permission::new('ROLE_TRAINING_REQUEST_APPROVE', "Approuver une demande de formation");
    yield Permission::new('ROLE_TRAINING_REQUEST_REJECT', "Rejeter une demande de formation");

    yield Permission::new('ROLE_TRAINING_SESSION_CREATE', "Créer une session de formation");
    yield Permission::new('ROLE_TRAINING_SESSION_LIST', "Consulter la liste des sessions de formation");
    yield Permission::new('ROLE_TRAINING_SESSION_DETAILS', "Consulter les détails d'une session de formation");
    yield Permission::new('ROLE_TRAINING_SESSION_UPDATE', "Modifier une session de formation");
    yield Permission::new('ROLE_TRAINING_SESSION_START', "Démarrer une session de formation");
    yield Permission::new('ROLE_TRAINING_SESSION_COMPLETE', "Clôturer une session de formation");
    yield Permission::new('ROLE_TRAINING_SESSION_CANCEL', "Annuler une session de formation");
    yield Permission::new('ROLE_TRAINING_SESSION_SET_PLANNED', "Remettre une session à l'état planifié");

    yield Permission::new('ROLE_TRAINING_ENROLLMENT_CREATE', "Créer une inscription à une session de formation");
    yield Permission::new('ROLE_TRAINING_ENROLLMENT_LIST', "Consulter la liste des inscriptions");
    yield Permission::new('ROLE_TRAINING_ENROLLMENT_DETAILS', "Consulter les détails d'une inscription");
    yield Permission::new('ROLE_TRAINING_ENROLLMENT_COMPLETE', "Marquer une inscription comme terminée");
    yield Permission::new('ROLE_TRAINING_ENROLLMENT_START', "Démarrer une inscription à une formation");
    yield Permission::new('ROLE_TRAINING_ENROLLMENT_CERTIFY', "Certifier une inscription à une formation");
    yield Permission::new('ROLE_TRAINING_ENROLLMENT_MARK_ABSENT', "Marquer une inscription comme absente");
    yield Permission::new('ROLE_TRAINING_ENROLLMENT_SET_ENROLLED', "Remettre une inscription à l'état assigné");

    yield Permission::new('ROLE_TRAINING_CATALOG_CREATE', "Créer une formation du catalogue");
    yield Permission::new('ROLE_TRAINING_CATALOG_LIST', "Consulter le catalogue de formations");
    yield Permission::new('ROLE_TRAINING_CATALOG_DETAILS', "Consulter les détails d'une formation du catalogue");
    yield Permission::new('ROLE_TRAINING_CATALOG_UPDATE', "Modifier une formation du catalogue");

    yield Permission::new('ROLE_JOB_ROLE_REQUIRED_TRAINING_CREATE', "Créer une formation requise pour une fiche métier");
    yield Permission::new('ROLE_JOB_ROLE_REQUIRED_TRAINING_LIST', "Consulter les formations requises par fiche métier");
    yield Permission::new('ROLE_JOB_ROLE_REQUIRED_TRAINING_DETAILS', "Consulter le détail d'une formation requise");
    yield Permission::new('ROLE_JOB_ROLE_REQUIRED_TRAINING_UPDATE', "Modifier une formation requise pour une fiche métier");

    yield Permission::new('ROLE_ONBOARDING_PROCESS_LIST', "Consulter la liste des processus d'onboarding");
    yield Permission::new('ROLE_ONBOARDING_PROCESS_DETAILS', "Consulter les détails d'un processus d'onboarding");
    yield Permission::new('ROLE_ONBOARDING_PROCESS_COMPLETE', "Clôturer un processus d'onboarding");
    yield Permission::new('ROLE_ONBOARDING_PROCESS_CANCEL', "Annuler un processus d'onboarding");

    yield Permission::new('ROLE_ONBOARDING_TASK_LIST', "Consulter la liste des tâches d'onboarding");
    yield Permission::new('ROLE_ONBOARDING_TASK_DETAILS', "Consulter les détails d'une tâche d'onboarding");
    yield Permission::new('ROLE_ONBOARDING_TASK_START', "Démarrer une tâche d'onboarding");
    yield Permission::new('ROLE_ONBOARDING_TASK_COMPLETE', "Terminer une tâche d'onboarding");
    yield Permission::new('ROLE_ONBOARDING_TASK_CANCEL', "Annuler une tâche d'onboarding");

    yield Permission::new('ROLE_EVALUATION_CYCLE_CREATE', "Créer un cycle d'évaluation");
    yield Permission::new('ROLE_EVALUATION_CYCLE_LIST', "Consulter la liste des cycles d'évaluation");
    yield Permission::new('ROLE_EVALUATION_CYCLE_DETAILS', "Consulter les détails d'un cycle d'évaluation");
    yield Permission::new('ROLE_EVALUATION_CYCLE_UPDATE', "Modifier un cycle d'évaluation");
    yield Permission::new('ROLE_EVALUATION_CYCLE_OPEN', "Ouvrir un cycle d'évaluation");
    yield Permission::new('ROLE_EVALUATION_CYCLE_CLOSE', "Clôturer un cycle d'évaluation");

    yield Permission::new('ROLE_PERFORMANCE_REVIEW_CREATE', "Créer une évaluation de performance");
    yield Permission::new('ROLE_PERFORMANCE_REVIEW_LIST', "Consulter la liste des évaluations de performance");
    yield Permission::new('ROLE_PERFORMANCE_REVIEW_DETAILS', "Consulter les détails d'une évaluation de performance");
    yield Permission::new('ROLE_PERFORMANCE_REVIEW_UPDATE', "Modifier une évaluation de performance");
    yield Permission::new('ROLE_PERFORMANCE_REVIEW_SUBMIT', "Soumettre une évaluation de performance");
    yield Permission::new('ROLE_PERFORMANCE_REVIEW_VALIDATE', "Valider une évaluation de performance");

    yield Permission::new('ROLE_OBJECTIVE_CREATE', "Créer un objectif SMART");
    yield Permission::new('ROLE_OBJECTIVE_LIST', "Consulter la liste des objectifs");
    yield Permission::new('ROLE_OBJECTIVE_DETAILS', "Consulter les détails d'un objectif");
    yield Permission::new('ROLE_OBJECTIVE_UPDATE', "Modifier un objectif");
    yield Permission::new('ROLE_OBJECTIVE_ACTIVATE', "Activer un objectif");
    yield Permission::new('ROLE_OBJECTIVE_COMPLETE', "Terminer un objectif");
    yield Permission::new('ROLE_OBJECTIVE_CANCEL', "Annuler un objectif");

    yield Permission::new('ROLE_EMPLOYEE_PROMOTION_ELIGIBILITY', "Consulter l'éligibilité promotion d'un employé");

    yield Permission::new('ROLE_CAREER_PLAN_CREATE', "Créer un plan de carrière");
    yield Permission::new('ROLE_CAREER_PLAN_LIST', "Consulter la liste des plans de carrière");
    yield Permission::new('ROLE_CAREER_PLAN_DETAILS', "Consulter les détails d'un plan de carrière");
    yield Permission::new('ROLE_CAREER_PLAN_UPDATE', "Modifier un plan de carrière");

    yield Permission::new('ROLE_MOBILITY_REQUEST_CREATE', "Créer une demande de mobilité");
    yield Permission::new('ROLE_MOBILITY_REQUEST_LIST', "Consulter la liste des demandes de mobilité");
    yield Permission::new('ROLE_MOBILITY_REQUEST_DETAILS', "Consulter les détails d'une demande de mobilité");
    yield Permission::new('ROLE_MOBILITY_REQUEST_SUBMIT', "Soumettre une demande de mobilité");
    yield Permission::new('ROLE_MOBILITY_REQUEST_APPROVE', "Approuver une demande de mobilité");
    yield Permission::new('ROLE_MOBILITY_REQUEST_REJECT', "Rejeter une demande de mobilité");
    yield Permission::new('ROLE_MOBILITY_REQUEST_CANCEL', "Annuler une demande de mobilité");

    yield Permission::new('ROLE_COMPENSATION_HISTORY_LIST', "Consulter l'historique salarial");
    yield Permission::new('ROLE_COMPENSATION_HISTORY_DETAILS', "Consulter le détail d'un historique salarial");
    yield Permission::new('ROLE_COMPENSATION_HISTORY_RECORD', "Enregistrer manuellement un changement de rémunération");

    yield Permission::new('ROLE_BENEFIT_CREATE', "Créer un avantage employeur");
    yield Permission::new('ROLE_BENEFIT_LIST', "Consulter la liste des avantages");
    yield Permission::new('ROLE_BENEFIT_DETAILS', "Consulter les détails d'un avantage");
    yield Permission::new('ROLE_BENEFIT_UPDATE', "Modifier un avantage employeur");

    yield Permission::new('ROLE_EMPLOYEE_BENEFIT_CREATE', "Attribuer un avantage à un employé");
    yield Permission::new('ROLE_EMPLOYEE_BENEFIT_LIST', "Consulter les avantages employés");
    yield Permission::new('ROLE_EMPLOYEE_BENEFIT_DETAILS', "Consulter le détail d'un avantage employé");
    yield Permission::new('ROLE_EMPLOYEE_BENEFIT_UPDATE', "Modifier un avantage employé");

    yield Permission::new('ROLE_EXIT_PROCESS_CREATE', "Créer un processus de sortie");
    yield Permission::new('ROLE_EXIT_PROCESS_LIST', "Consulter les processus de sortie");
    yield Permission::new('ROLE_EXIT_PROCESS_DETAILS', "Consulter le détail d'un processus de sortie");
    yield Permission::new('ROLE_EXIT_PROCESS_START', "Démarrer un processus de sortie");
    yield Permission::new('ROLE_EXIT_PROCESS_COMPLETE', "Clôturer un processus de sortie");
    yield Permission::new('ROLE_EXIT_PROCESS_CANCEL', "Annuler un processus de sortie");

    yield Permission::new('ROLE_EXIT_TASK_LIST', "Consulter les tâches de sortie");
    yield Permission::new('ROLE_EXIT_TASK_DETAILS', "Consulter le détail d'une tâche de sortie");
    yield Permission::new('ROLE_EXIT_TASK_START', "Démarrer une tâche de sortie");
    yield Permission::new('ROLE_EXIT_TASK_COMPLETE', "Terminer une tâche de sortie");
    yield Permission::new('ROLE_EXIT_TASK_CANCEL', "Annuler une tâche de sortie");

    yield Permission::new('ROLE_SUCCESSION_PLAN_CREATE', "Créer un plan de succession");
    yield Permission::new('ROLE_SUCCESSION_PLAN_LIST', "Consulter les plans de succession");
    yield Permission::new('ROLE_SUCCESSION_PLAN_DETAILS', "Consulter le détail d'un plan de succession");
    yield Permission::new('ROLE_SUCCESSION_PLAN_UPDATE', "Modifier un plan de succession");

    yield Permission::new('ROLE_HR_DASHBOARD_VIEW', "Consulter le tableau de bord RH");

};
