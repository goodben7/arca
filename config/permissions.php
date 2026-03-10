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

    yield Permission::new('ROLE_WORK_EXPERIENCE_CREATE', "Créer une expérience professionnelle");
    yield Permission::new('ROLE_WORK_EXPERIENCE_LIST', "Consulter la liste des expériences professionnelles");
    yield Permission::new('ROLE_WORK_EXPERIENCE_DETAILS', "Consulter les détails d'une expérience professionnelle");
    yield Permission::new('ROLE_WORK_EXPERIENCE_UPDATE', "Modifier une expérience professionnelle");

    yield Permission::new('ROLE_SKILL_CREATE', "Créer une compétence");
    yield Permission::new('ROLE_SKILL_LIST', "Consulter la liste des compétences");
    yield Permission::new('ROLE_SKILL_DETAILS', "Consulter les détails d'une compétence");
    yield Permission::new('ROLE_SKILL_UPDATE', "Modifier une compétence");

    yield Permission::new('ROLE_DOC_CREATE', "Créer un document");
    yield Permission::new('ROLE_DOC_LIST', "Consulter la liste des documents");
    yield Permission::new('ROLE_DOC_DETAILS', "Consulter les détails d'un document");
    yield Permission::new('ROLE_DOC_DELETE', "Supprimer un document");

    yield Permission::new('ROLE_DEPARTMENT_CREATE', "Créer un département");
    yield Permission::new('ROLE_DEPARTMENT_LIST', "Consulter la liste des départements");
    yield Permission::new('ROLE_DEPARTMENT_DETAILS', "Consulter les détails d'un département");
    yield Permission::new('ROLE_DEPARTMENT_UPDATE', "Modifier un département");

    yield Permission::new('ROLE_CONTRACT_CREATE', "Créer un contrat");
    yield Permission::new('ROLE_CONTRACT_LIST', "Consulter la liste des contrats");
    yield Permission::new('ROLE_CONTRACT_DETAILS', "Consulter les détails d'un contrat");
    yield Permission::new('ROLE_CONTRACT_UPDATE', "Modifier un contrat");

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

};
