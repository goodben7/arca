# Arca — Guide d'intégration front-end

Guide de référence pour intégrer une application front (React, Vue, Angular, mobile…) sur l'API REST **Arca** (SIRH).

**Stack backend :** Symfony 8 · API Platform 4 · Doctrine · JWT (Lexik)  
**Préfixe API :** `/api`  
**Format par défaut :** JSON (`application/json`) — sauf upload de documents (multipart)

---

## Table des matières

1. [Démarrage rapide](#1-démarrage-rapide)
2. [Conventions API](#2-conventions-api)
3. [Authentification & permissions](#3-authentification--permissions)
4. [Pattern des actions métier](#4-pattern-des-actions-métier)
5. [Ordre d'intégration recommandé](#5-ordre-dintégration-recommandé)
6. [Modules par domaine](#6-modules-par-domaine)
7. [Timeline employé (Journey)](#7-timeline-employé-journey)
8. [Pièges connus](#8-pièges-connus)
9. [Référence des préfixes ID](#9-référence-des-préfixes-id)
10. [Annexes](#10-annexes)

---

## 1. Démarrage rapide

### 1.1 Environnement local

```bash
# Dépendances
composer install

# Base de données + migrations
php bin/console doctrine:migrations:migrate

# Données de démo (ordre recommandé)
php bin/console ar:seed:profiles
php bin/console ac:create-user   # créer un utilisateur admin
php bin/console app:seed:job-architecture
php bin/console app:seed:skills
php bin/console ar:seed:departments
php bin/console ar:seed:positions

# Serveur
symfony server:start
# ou: php -S localhost:8000 -t public
```

### 1.2 Authentification JWT

```http
POST /api/authentication_token
Content-Type: application/json

{
  "username": "admin@example.com",
  "password": "secret"
}
```

**Réponse :**

```json
{ "token": "eyJ0eXAiOiJKV1QiLCJhbG..." }
```

Le champ `username` accepte **email ou téléphone** (`MultiFieldUserProvider`).

**Toutes les requêtes suivantes :**

```http
Authorization: Bearer <token>
Accept: application/json
Content-Type: application/json
```

### 1.3 Utilisateur courant

```http
GET /api/users/about
Authorization: Bearer <token>
```

Retourne le profil de l'utilisateur connecté (rôles, permissions effectives via le `Profile`).

### 1.4 Catalogue des permissions

```http
GET /api/permissions
```

Liste tous les rôles disponibles (`ROLE_EMPLOYEE_CREATE`, etc.) — utile pour construire l'UI d'administration des profils.

---

## 2. Conventions API

### 2.1 Identifiants

Chaque ressource a un préfixe + timestamp généré côté serveur :

| Exemple | Ressource |
|---------|-----------|
| `EM…` | Employee |
| `JR…` | JobRole |
| `LR…` | LeaveRequest |
| `DC…` | Document |

Les relations se passent par **ID string** (pas d'IRI obligatoire côté front si vous utilisez `application/json`).

### 2.2 CRUD standard

| Méthode | Route | Usage |
|---------|-------|-------|
| `GET` | `/api/{resources}` | Collection (paginée) |
| `GET` | `/api/{resources}/{id}` | Détail |
| `POST` | `/api/{resources}` | Création |
| `PATCH` | `/api/{resources}/{id}` | Mise à jour partielle (`application/merge-patch+json`) |
| `DELETE` | `/api/{resources}/{id}` | Suppression |

Noms de collections API Platform (snake_case pluriel) : `employees`, `leave_requests`, `onboarding_tasks`, etc.

### 2.3 Filtres & pagination

**Recherche** (SearchFilter) — query params :

```
GET /api/employees?status=ACTIVE&jobRole=JRxxx
GET /api/leave_requests?employee=EMxxx&status=PENDING
GET /api/documents?holderId=EMxxx&holderType=EMPLOYEE
```

Suffixes possibles selon le filtre : `exact` (défaut), `ipartial`, `start`.

**Pagination** (activable côté client) :

```
GET /api/employees?pagination=true&page=1&itemsPerPage=30
```

**Tri** (OrderFilter quand configuré) :

```
GET /api/employees/{id}/journey?order[occurredAt]=desc
```

### 2.4 Formats de réponse

- **JSON simple :** `Accept: application/json`
- **JSON-LD / Hydra :** `Accept: application/ld+json` — collections avec `hydra:member`, `hydra:totalItems`

### 2.5 Erreurs

| Code | Signification typique |
|------|----------------------|
| `401` | Token absent ou expiré |
| `403` | Rôle insuffisant (`is_granted`) |
| `404` | Ressource introuvable ou `employeeId` invalide |
| `422` | Validation Symfony (champs DTO) |
| `415` | Mauvais `Content-Type` (ex. JSON sur upload document) |

Corps d'erreur API Platform : `violations[]` avec `propertyPath` et `message`.

### 2.6 PATCH

Utiliser le merge patch :

```http
PATCH /api/employees/EMxxx
Content-Type: application/merge-patch+json

{ "phone": "+33600000000" }
```

Seuls les champs exposés dans les groupes de sérialisation `*:patch` sont modifiables.

---

## 3. Authentification & permissions

### 3.1 Modèle

```
User → Profile → permissions (ROLE_*)
```

Les opérations API vérifient `is_granted("ROLE_XXX")`. Le front doit :

1. Charger `/api/users/about` au login
2. Masquer/désactiver les actions sans le rôle correspondant
3. Gérer les `403` gracieusement

### 3.2 Rôles par domaine (extrait)

| Domaine | Rôles clés |
|---------|------------|
| Utilisateurs | `ROLE_USER_*`, `ROLE_PROFILE_*` |
| Employés | `ROLE_EMPLOYEE_*`, `ROLE_EMPLOYEE_JOURNEY_LIST`, `ROLE_EMPLOYEE_PROMOTION_ELIGIBILITY` |
| Contrats & congés | `ROLE_CONTRACT_*`, `ROLE_LEAVE_REQUEST_*` |
| Documents | `ROLE_DOC_*` (⚠️ GET/POST actuellement sans garde — voir §8) |
| Référentiel | `ROLE_DEPARTMENT_*`, `ROLE_JOB_*`, `ROLE_GRADE_*`, `ROLE_POSITION_*` |
| Compétences | `ROLE_SKILL_*`, `ROLE_EMPLOYEE_SKILL_*` |
| Recrutement | `ROLE_RECRUITMENT_REQUEST_*`, `ROLE_JOB_OFFER_*`, `ROLE_APPLICATION_*` |
| Onboarding | `ROLE_ONBOARDING_PROCESS_*`, `ROLE_ONBOARDING_TASK_*` |
| Performance | `ROLE_EVALUATION_CYCLE_*`, `ROLE_PERFORMANCE_REVIEW_*`, `ROLE_OBJECTIVE_*` |
| Formation | `ROLE_TRAINING_*` |
| Mobilité | `ROLE_CAREER_PLAN_*`, `ROLE_MOBILITY_REQUEST_*` |
| Compensation | `ROLE_COMPENSATION_HISTORY_*` |
| Avantages & sortie | `ROLE_BENEFIT_*`, `ROLE_EMPLOYEE_BENEFIT_*`, `ROLE_EXIT_*` |
| Pilotage | `ROLE_SUCCESSION_PLAN_*`, `ROLE_HR_DASHBOARD_VIEW` |
| Sanctions | `ROLE_SANCTION_SCALE_*`, `ROLE_DISCIPLINARY_CASE_*` |

Liste complète : `GET /api/permissions` ou `config/permissions.php`.

---

## 4. Pattern des actions métier

Arca sépare **CRUD** et **transitions de workflow**.

### 4.1 CRUD

`POST /api/leave_requests` → création en brouillon  
`PATCH /api/leave_requests/{id}` → modification tant que l'état le permet

### 4.2 Actions workflow (POST dédiés)

Les transitions métier passent par des **routes POST** avec un **DTO** dédié — jamais par un PATCH de statut :

```http
POST /api/leave_requests/approvals
Content-Type: application/json

{ "leaveRequestId": "LRxxx" }
```

```http
POST /api/leave_requests/rejections
Content-Type: application/json

{ "leaveRequestId": "LRxxx", "raison": "Effectif insuffisant" }
```

**Convention générale :**

| Pattern route | Exemple | Effet |
|---------------|---------|-------|
| `/{resources}/approvals` | leave, recruitment, training, mobility | Approbation |
| `/{resources}/rejections` | idem | Rejet (+ raison souvent requise) |
| `/{resources}/completions` | onboarding, objectives, exit | Clôture |
| `/{resources}/cancellations` | onboarding, mobility, exit | Annulation |
| `/{resources}/starts` | onboarding task, exit task, training | Démarrage |
| `/{resources}/submissions` | performance review, mobility | Soumission |
| `/{resources}/validations` | performance review, employee skill | Validation |

La plupart de ces POST retournent **200** avec la ressource mise à jour (pas systématiquement 201).

### 4.3 Ressources sans POST create

Certaines entités sont créées **automatiquement** par le backend :

| Ressource | Création |
|-----------|----------|
| `OnboardingProcess` | Auto à la création d'employé (`EmployeeCreatedEvent`) |
| `OnboardingTask` | Auto avec la checklist (`config/onboarding_checklist.php`) |
| `EmployeeJourneyEntry` | Auto via domain events (lecture seule) |
| `CompensationHistory` (auto) | Sur mobilité implémentée — sinon POST manuel `recordings` |

---

## 5. Ordre d'intégration recommandé

Intégrer par couches de dépendance :

```
Phase A — Socle
  Auth JWT → users/about → permissions → profiles

Phase B — Référentiel RH
  departments → job_families → grades → job_roles → career_paths → positions
  skill_categories → skills → job_role_required_skills

Phase C — Cœur employé
  employees (CRUD + lifecycle actions) → contracts → work_experiences
  employee_skills → documents (multipart)

Phase D — Parcours & temps
  leave_requests → journey (timeline)

Phase E — Recrutement → Onboarding
  recruitment_requests → job_offers → applications → hire
  onboarding_processes / onboarding_tasks (workflow)

Phase F — Développement RH
  training_catalog → training_requests → sessions → enrollments
  evaluation_cycles → objectives → performance_reviews
  promotion-eligibility

Phase G — Carrière & rémunération
  career_plans → mobility_requests
  compensation_histories (lecture + enregistrement manuel)

Phase H — Avantages & sortie
  benefits → employee_benefits
  exit_processes → exit_tasks

Phase I — Pilotage
  succession_plans → hr/dashboard
```

---

## 6. Modules par domaine

### 6.1 Utilisateurs & profils

| Route | Méthode | Rôle | Body |
|-------|---------|------|------|
| `/api/users` | GET | `ROLE_USER_LIST` | — |
| `/api/users/{id}` | GET | `ROLE_USER_DETAILS` | — |
| `/api/users` | POST | `ROLE_USER_CREATE` | `CreateUserDto` |
| `/api/users/{id}` | PATCH | `ROLE_USER_EDIT` | `UpdateUserDto` |
| `/api/users/{id}/credentials` | PATCH | `ROLE_USER_CHANGE_PWD` | `ChangePasswordDto` |
| `/api/users/{id}` | DELETE | `ROLE_USER_DELETE` | — |
| `/api/users/{id}/lock_toggle` | POST | `ROLE_USER_LOCK` | — |
| `/api/users/about` | GET | `ROLE_USER` | — |
| `/api/profiles` | CRUD | `ROLE_PROFILE_*` | entité Profile |

---

### 6.2 Employés

**Création** — `POST /api/employees` (`CreateEmployeeDto`) :

```json
{
  "firstName": "Marie",
  "lastName": "Dupont",
  "email": "marie@corp.com",
  "gender": "F",
  "hireDate": "2026-06-01",
  "jobRole": "JR…",
  "grade": "GR…",
  "department": "IT",
  "managerId": "EM…"
}
```

> Nouvel employé : statut `INACTIVE` si onboarding auto déclenché.

**Actions lifecycle** (toutes en POST + DTO avec `employeeId`) :

| Route | Rôle | Statuts concernés |
|-------|------|-------------------|
| `/api/employees/activations` | `ROLE_EMPLOYEE_ACTIVATE` | → ACTIVE |
| `/api/employees/deactivations` | `ROLE_EMPLOYEE_DEACTIVATE` | → INACTIVE |
| `/api/employees/on_leaves` | `ROLE_EMPLOYEE_SET_ON_LEAVE` | → ON_LEAVE |
| `/api/employees/suspensions` | `ROLE_EMPLOYEE_SUSPEND` | → SUSPENDED |
| `/api/employees/terminations` | `ROLE_EMPLOYEE_TERMINATE` | → TERMINATED |
| `/api/employees/retirements` | `ROLE_EMPLOYEE_RETIRE` | → RETIRED (voir §6.2.1) |
| `/api/employees/probations` | `ROLE_EMPLOYEE_SET_PROBATION` | → PROBATION |
| `/api/employees/assign-manager` | `ROLE_EMPLOYEE_ASSIGN_MANAGER` | managerId |

#### 6.2.1 Retraite (éligibilité + action)

**Règle métier :** un employé peut partir à la retraite si **âge ≥ 65 ans** **OU** **ancienneté entreprise ≥ 35 ans**.

- `birthDate` peut être manquant → seule la règle d’ancienneté (`hireDate`) s’applique
- `hireDate` est obligatoire côté modèle

**Pré-check (recommandé avant d’afficher le bouton) :**

```http
GET /api/employees/{employeeId}/retirement-eligibility
Authorization: Bearer <token>
```

Rôle : `ROLE_EMPLOYEE_RETIRE`

Réponse :

```json
{
  "employeeId": "EMxxx",
  "eligible": false,
  "reasons": [
    "age requires >= 780 months",
    "tenure requires >= 420 months",
    "retirement requires age >= 65 years OR career >= 35 years"
  ]
}
```

ou si OK :

```json
{
  "employeeId": "EMxxx",
  "eligible": true,
  "reasons": []
}
```

**Action de retraite :**

```http
POST /api/employees/retirements
Content-Type: application/json

{ "employeeId": "EMxxx" }
```

- **200** → employé `status: RETIRED` (+ `retiredAt`, `retiredBy`, `departureDate`)
- **400** → non éligible (mêmes raisons que le pré-check)
- **403** → rôle manquant

**UI recommandée :**

1. Sur la fiche employé, appeler `GET .../retirement-eligibility`
2. Si `eligible === false` → bouton désactivé + tooltip avec `reasons`
3. Si `eligible === true` → confirmation puis `POST .../retirements`
4. Rafraîchir `GET /employees/{id}` et `GET /employees/{id}/journey`

**Journey après retraite :**

```http
GET /api/employees/{employeeId}/journey?order[occurredAt]=desc
```

Nouvelle entrée :

| Champ | Valeur |
|-------|--------|
| `stage` | `RETIREMENT` |
| `eventType` | `RETIRED` |
| `description` | `employee retired` |

> Différence avec offboarding : `POST /employees/retirements` = transition immédiate de statut.  
> Le process `ExitProcess` avec motif `RETIREMENT` reste le parcours checklist complet (fin contrat, lock user, etc.).

**Filtres :** `id`, `employeeNumber`, `email`, `status`, `jobRole`, `grade`, …

**Expériences pro :** `POST /api/work_experiences` (`CreateWorkExperienceDto`), `PATCH` direct.

---

### 6.3 Référentiel job architecture

| Ressource | Préfixe | Notes |
|-----------|---------|-------|
| `departments` | DP | GET souvent ouvert |
| `job_families` | JF | |
| `grades` | GR | `rank` pour compensation |
| `job_roles` | JR | Fiche métier (titre, famille, grade) |
| `career_paths` | CP | `fromJobRole` → `toJobRole` + conditions JSON |
| `positions` | PO | Slot budgétaire (`headcount`, `openPositions`) |

**Actions positions :**

- `POST /api/positions/open` — `OpenPositionDto`
- `POST /api/positions/close` — `ClosePositionDto`

> `JobRole` = fiche métier. `Position` = poste organisationnel ouvert/fermé.

---

### 6.4 Compétences

| Route | Description |
|-------|-------------|
| `GET/POST/PATCH /api/skill_categories` | Catalogue |
| `GET/POST/PATCH /api/skills` | Compétences |
| `GET/POST/PATCH /api/job_role_required_skills` | Exigences par fiche métier |
| `POST /api/employee_skills` | Assigner (`CreateEmployeeSkillDto`) |
| `POST /api/employee_skills/validations` | Valider (`ValidateEmployeeSkillDto`) |
| `PATCH /api/employee_skills/{id}` | Niveau / validatedAt |

Niveaux : `BEGINNER` → `INTERMEDIATE` → `ADVANCED` → `EXPERT`.

---

### 6.5 Contrats

CRUD + workflow :

| Route | Transition |
|-------|------------|
| `POST /api/contracts/activations` | → ACTIVE |
| `POST /api/contracts/endings` | → ENDED |
| `POST /api/contracts/cancellations` | → CANCELLED |
| `POST /api/contracts/pendings` | → PENDING |

Filtres : `employee`, `type`, `status`.

---

### 6.6 Congés (`LeaveRequest`)

| Route | Méthode | Rôle |
|-------|---------|------|
| `/api/leave_requests` | GET/POST | LIST/CREATE |
| `/api/leave_requests/{id}` | GET/PATCH | DETAILS/UPDATE |
| `/api/leave_requests/approvals` | POST | APPROVE — `{ "leaveRequestId": "LR…" }` |
| `/api/leave_requests/rejections` | POST | REJECT — `{ "leaveRequestId": "LR…", "raison": "…" }` |

Workflow : `PENDING` → `APPROVED` | `REJECTED`.

Types : `ANNUAL`, `SICK`, `MATERNITY`, `UNPAID`, `OTHER` (voir `LeaveRequestConstants`).

---

### 6.7 Documents

⚠️ **POST uniquement en `multipart/form-data`** — pas de JSON.

```javascript
const form = new FormData();
form.append('type', 'CV');
form.append('title', 'CV Marie Dupont');
form.append('holderId', 'EMxxx');
form.append('holderType', 'EMPLOYEE');  // ou APPLICATION, etc.
form.append('file', fileBlob, 'cv.pdf');

await fetch('/api/documents', {
  method: 'POST',
  headers: { Authorization: `Bearer ${token}` },
  // NE PAS mettre Content-Type — le navigateur ajoute le boundary
  body: form,
});
```

Types document : `CV`, `DIPL`, `CNTR`, `ID`, `PAY`, `OTHER`, … (constantes dans `Document.php`).

`GET /api/documents?holderId=EMxxx&holderType=EMPLOYEE` — filtrer par détenteur.

Pas de PATCH — création + suppression (`ROLE_DOC_DELETE`) uniquement.

---

### 6.8 Recrutement

**Demande de recrutement** `recruitment_requests` (RR) :

- CRUD partiel + `POST /recruitment_requests/approvals` / `rejections`
- Workflow : `DRAFT` → `PENDING_APPROVAL` → `APPROVED` | `REJECTED`

**Offre d'emploi** `job_offers` (JO) :

- `POST /job_offers/publications` — publier
- `POST /job_offers/closures` — clôturer
- `POST /job_offers/drafts` — repasser en brouillon

**Candidature** `applications` (AP) :

| Route | Transition |
|-------|------------|
| `POST /applications/applied` | APPLIED |
| `POST /applications/shortlistings` | SHORTLISTED |
| `POST /applications/interviews` | INTERVIEW |
| `POST /applications/rejections` | REJECTED |
| `POST /applications/hirings` | HIRED → crée l'employé + onboarding |

**Embauche** — `POST /api/applications/hirings` :

```json
{ "applicationId": "APxxx" }
```

Enchaînement backend : `ApplicationHiredEvent` → `Employee` créé (`INACTIVE`) → `OnboardingProcess` auto.

---

### 6.9 Onboarding

**Pas de POST pour créer un processus** — déclenché à l'embauche ou création employé.

Checklist par défaut (5 tâches) : dossier admin, pièces d'identité, accès IT, matériel, formation sécurité.

| Route | Rôle | Body |
|-------|------|------|
| `GET /api/onboarding_processes` | LIST | `?employee=EMxxx` |
| `GET /api/onboarding_processes/{id}` | DETAILS | — |
| `POST /api/onboarding_processes/completions` | COMPLETE | `{ "processId": "OP…" }` |
| `POST /api/onboarding_processes/cancellations` | CANCEL | `{ "processId": "OP…" }` |
| `GET /api/onboarding_tasks` | LIST | `?process=OPxxx` |
| `POST /api/onboarding_tasks/starts` | START | `{ "taskId": "OT…" }` |
| `POST /api/onboarding_tasks/completions` | COMPLETE | `{ "taskId": "OT…" }` |
| `POST /api/onboarding_tasks/cancellations` | CANCEL | `{ "taskId": "OT…" }` |

**Workflow tâche :** `PENDING` → `IN_PROGRESS` → `COMPLETED` | `CANCELLED`

**Workflow processus :** idem. Quand toutes les tâches sont terminées → complétion auto possible.

**Fin onboarding :** `OnboardingCompletedEvent` → employé `INACTIVE` passe `ACTIVE`.

**Types de tâches :** `DOCUMENT`, `IT_ACCESS`, `TRAINING`, `EQUIPMENT`, `HR_FORM`.

**UI suggérée :**

1. Fiche employé → onglet Onboarding
2. `GET onboarding_processes?employee={id}`
3. `GET onboarding_tasks?process={processId}`
4. Boutons Start / Complete par tâche
5. Barre de progression (% tâches COMPLETED)
6. Rafraîchir `GET /employees/{id}` après complétion (statut ACTIVE)

---

### 6.10 Performance & objectifs

**Cycles** `evaluation_cycles` (EC) : `DRAFT` → `OPEN` → `CLOSED`

- `POST /evaluation_cycles/opens`, `/closures`

**Évaluations** `performance_reviews` (PV) : `DRAFT` → `SUBMITTED` → `VALIDATED`

- `POST /performance_reviews/submissions`, `/validations`

**Objectifs** `objectives` (OB) :

- `POST /objectives/activations`, `/completions`, `/cancellations`

**Éligibilité promotion** (lecture seule) :

```http
GET /api/employees/{employeeId}/promotion-eligibility?targetJobRole=JRxxx
```

Retourne `{ eligible: bool, reasons: string[] }` — agrège policies (career path, ancienneté, perf, skills, formations).

---

### 6.11 Formation

| Ressource | Rôle |
|-----------|------|
| `training_catalogs` (TC) | Référentiel formations |
| `job_role_required_trainings` (JRT) | Formations obligatoires par métier |
| `training_requests` (TR) | Demande dept + approve/reject |
| `training_sessions` (TS) | Planification, start/complete/cancel |
| `training_enrollments` (TE) | Par employé : start → complete → certify |

Workflow inscription : `ASSIGNED` → `IN_PROGRESS` → `COMPLETED` → `CERTIFIED` (+ `ABSENT`).

Actions : `/starts`, `/completions`, `/certifications`, `/absences`, `/enrollments`.

---

### 6.12 Carrière & mobilité

**Plan de carrière** `career_plans` (PL) — intention long terme (targetJobRole, targetGrade, targetDate).

**Demande de mobilité** `mobility_requests` (MB) :

Types : `TRANSFER`, `PROMOTION`, `DEMOTION`, `SECONDEMENT`.

```
DRAFT → MANAGER_APPROVAL → HR_APPROVAL → EXECUTIVE_APPROVAL → IMPLEMENTED
         ↘ REJECTED / CANCELLED
```

| Route | Effet |
|-------|-------|
| `POST /mobility_requests` | Création brouillon |
| `POST /mobility_requests/submissions` | Soumission (policy si PROMOTION) |
| `POST /mobility_requests/approvals` | Palier suivant |
| `POST /mobility_requests/rejections` | Rejet |
| `POST /mobility_requests/cancellations` | Annulation (DRAFT) |

À `IMPLEMENTED` : mise à jour auto `Employee.jobRole`, `grade`, `department` + événement journey.

---

### 6.13 Compensation

Lecture seule sauf enregistrement manuel :

```http
POST /api/compensation_histories/recordings
{ "employeeId": "EM…", "newSalary": 45000, "effectiveDate": "2026-07-01", "reason": "…" }
```

Historique auto sur mobilité promotion/demotion. Met à jour le contrat actif + notification payroll async.

`GET /api/compensation_histories?employee=EMxxx`

---

### 6.14 Avantages employé

| Ressource | Description |
|-----------|-------------|
| `benefits` (BF) | Catalogue : `HEALTH`, `TRANSPORT`, `MEAL`, `OTHER` |
| `employee_benefits` (EB) | Inscription employé (startDate, endDate, status) |

---

### 6.15 Offboarding (symétrique onboarding)

| Route | Description |
|-------|-------------|
| `POST /api/exit_processes` | Créer (`CreateExitProcessDto` : employee, reason, departureDate) |
| `POST /api/exit_processes/starts` | Démarre + crée les `exit_tasks` |
| `POST /api/exit_tasks/starts\|completions\|cancellations` | Checklist |
| `POST /api/exit_processes/completions` | Orchestration finale |

Motifs : `RESIGNATION`, `DISMISSAL`, `RETIREMENT`, `END_OF_CONTRACT`.

À la complétion : fin de contrat, terminate/retire employé, lock user, clôture avantages.

Checklist : `config/offboarding_checklist.php`.

---

### 6.16 Succession & dashboard RH

**Plans de succession** `succession_plans` (SP) :

- `criticalJobRole`, `candidate`, `readinessLevel` (`READY_NOW`, `WITHIN_1_YEAR`, …)
- Rôles critiques : `config/succession.php` (CFO, ACC-LEAD, …)

**Dashboard** — `GET /api/hr/dashboard` (`ROLE_HR_DASHBOARD_VIEW`) :

```json
{
  "headcount": 42,
  "departuresLast12Months": 3,
  "turnoverRatePercent": 7.1,
  "promotionsLast12Months": 5,
  "trainingsInProgress": 12,
  "trainingsCertifiedLast12Months": 28,
  "criticalJobRolesTotal": 3,
  "criticalJobRolesCovered": 2,
  "successionCoveragePercent": 66.7,
  "criticalSkillGaps": 4,
  "periodMonths": 12,
  "computedAt": "2026-06-26T10:00:00+00:00"
}
```

---

### 6.17 Sanctions

#### 6.17.1 Échelle des sanctions (référentiel — Phase A)

| Ressource | Préfixe | Description |
|-----------|---------|-------------|
| `sanction_scales` | SS | Niveau de sanction (`code`, `label`, `severityLevel` 1–5, `requiresHearing`, `maxDurationDays`, `active`) |

```http
GET /api/sanction_scales
GET /api/sanction_scales?active=true&order[severityLevel]=asc
POST /api/sanction_scales
PATCH /api/sanction_scales/{id}
```

Rôles : `ROLE_SANCTION_SCALE_LIST|DETAILS|CREATE|UPDATE`

Seed : `php bin/console app:seed:sanction-scales` (idempotent) → `REPRIMAND` (1), `WARN` (2), `BLAME` (3), `SUSPEND` (4), `DISMISS` (5).

#### 6.17.2 Affaire disciplinaire (workflow — Phase B)

| Ressource | Préfixe | Description |
|-----------|---------|-------------|
| `disciplinary_cases` | DS | Procédure disciplinaire (machine à états, pas de PATCH statut) |

**Statuts**

```
DRAFT → OPENED → EXPLANATION_REQUESTED → HEARING_SCHEDULED → DECISION_PENDING → SANCTION_APPLIED → CLOSED
                                  ↘ CANCELLED / REJECTED
```

Mapping Code du travail RDC :

| Étape légale | Statut / action |
|--------------|-----------------|
| Constat de la faute | `DRAFT` → `OPENED` |
| Demande d’explications | `POST …/explanations` → `EXPLANATION_REQUESTED` |
| Entretien préalable | `POST …/hearings` (si `requiresHearing`) |
| Notification de sanction | `POST …/applications` (`appealDeadlineAt` = +8 jours) |
| Délais légaux de recours | champ `appealDeadlineAt` ; clôture = `POST …/closures` |

Si `requiresHearing = false` (REPRIMAND, WARN) : après explications → `decisions` (pas d’entretien).

**Routes** (POST dédiés + DTO, pattern ExitProcess)

```http
POST /api/disciplinary_cases
POST /api/disciplinary_cases/openings
POST /api/disciplinary_cases/explanations
POST /api/disciplinary_cases/hearings
POST /api/disciplinary_cases/decisions
POST /api/disciplinary_cases/applications
POST /api/disciplinary_cases/cancellations
POST /api/disciplinary_cases/rejections
POST /api/disciplinary_cases/closures
GET  /api/disciplinary_cases
GET  /api/disciplinary_cases/{id}
```

Rôles : `ROLE_DISCIPLINARY_CASE_CREATE|LIST|DETAILS|OPEN|REQUEST_EXPLANATION|SCHEDULE_HEARING|DECIDE|APPLY|CANCEL|REJECT|CLOSE`

**Création**

```json
{
  "employee": "EM…",
  "sanctionScale": "SS…",
  "facts": "Faits reprochés…",
  "occurredAt": "2026-07-01T10:00:00+00:00",
  "reason": null,
  "acknowledgeRecidivism": false
}
```

`acknowledgeRecidivism` : obligatoire à `true` pour rester au même palier qu’une sanction déjà appliquée (voir 6.17.3). L’escalade (gravité supérieure) ne le demande pas.

**Demande d’explications**

```json
{
  "disciplinaryCaseId": "DS…",
  "explanationDueAt": "2026-08-26T10:00:00+00:00",
  "explanationText": "Réponse de l’employé (optionnel)"
}
```

`explanationDueAt` omis → +8 jours. Le même POST, déjà en `EXPLANATION_REQUESTED`, enregistre `explanationText` (réponse) sans changer le statut.

**Transitions** (corps typique `{ "disciplinaryCaseId": "DS…" }` ; `hearings` ajoute `hearingAt` ; `decisions`/`rejections` peuvent ajouter `reason` ; `decisions` accepte aussi `acknowledgeRecidivism`)

| Action | Depuis | Vers |
|--------|--------|------|
| openings | DRAFT | OPENED |
| explanations | OPENED | EXPLANATION_REQUESTED |
| hearings | EXPLANATION_REQUESTED (si `requiresHearing`) | HEARING_SCHEDULED |
| decisions | EXPLANATION_REQUESTED (sans hearing) ou HEARING_SCHEDULED | DECISION_PENDING |
| applications | DECISION_PENDING | SANCTION_APPLIED (`appealDeadlineAt` +8 j) |
| closures | SANCTION_APPLIED | CLOSED |
| cancellations / rejections | avant application | CANCELLED / REJECTED |

**Effets transverses**

- Journey : `DISCIPLINARY_STARTED` à l’ouverture, `SANCTION_APPLIED` à l’application (stage `DISCIPLINARY`)
- Si échelle `SUSPEND` → `EmployeeManager::suspendFrom` à l’application
- Si échelle `DISMISS` → création **et démarrage** automatique d’un `ExitProcess` (`REASON_DISMISSAL`, `departureDate` = aujourd’hui, statut `IN_PROGRESS`) ; exposé dans `exitProcess`
- Si échelle `WARN` / `BLAME` / `REPRIMAND` → création d’un `Document` `TYPE_WARNING_LETTER` (métadonnée) ; fichier optionnel via `multipart/form-data` sur `applications` (champ `file`)
- Création refusée si une affaire active existe déjà pour l’employé (statuts non terminaux)

**Apply avec lettre (multipart)**

```http
POST /api/disciplinary_cases/applications
Content-Type: multipart/form-data

disciplinaryCaseId=DS…
file=<pdf|image>
```

Sans fichier, JSON classique reste accepté :

```json
{ "disciplinaryCaseId": "DS…" }
```

#### 6.17.3 Récidives et synthèse (Phase C)

La récidive n’est pas un simple compteur : à la **création** et à la **décision**, l’API lit l’historique appliqué et **propose un palier plus élevé** ou **bloque**.

Règle (gravité max déjà appliquée = `maxSeverityLevel`) :

| Proposition | Comportement |
|-------------|--------------|
| Aucune sanction appliquée | Tout palier autorisé |
| Gravité **>** max | Autorisé (escalade) — pas d’`acknowledgeRecidivism` |
| Gravité **=** max | **400** sauf `acknowledgeRecidivism: true` (même palier assumé) |
| Gravité **<** max | **400** toujours (pas de désescalade) |
| Déjà au max (`DISMISS`) | Même palier uniquement avec `acknowledgeRecidivism: true` |

Afficher le summary **avant** le formulaire de création / décision. Si `requiresAcknowledgement`, proposer `suggestedNextCode` et n’envoyer `acknowledgeRecidivism: true` que si l’utilisateur confirme explicitement le même palier.

Le flag doit être renvoyé **à la création et à la décision** (la décision revalide la règle).

```http
GET /api/employees/{employeeId}/disciplinary-summary
```

Rôle : `ROLE_DISCIPLINARY_CASE_LIST`

Réponse (`disciplinary_summary:get`) :

| Champ | Type | Description |
|-------|------|-------------|
| `employeeId` | string | Identifiant employé |
| `appliedSanctionCount` | int | Nombre de sanctions appliquées (statuts `SANCTION_APPLIED` ou `CLOSED`) |
| `maxSeverityLevel` | int? | Gravité max parmi les sanctions appliquées |
| `lastSanctionCode` | string? | Code de la dernière échelle appliquée |
| `lastSanctionLabel` | string? | Libellé de la dernière échelle appliquée |
| `lastAppliedAt` | datetime? | Date d’application de la dernière sanction |
| `hasActiveCase` | bool | Affaire disciplinaire active en cours |
| `isRepeatOffender` | bool | `appliedSanctionCount >= 1` |
| `requiresAcknowledgement` | bool | Un même palier exigerait `acknowledgeRecidivism` |
| `suggestedNextSeverity` | int? | Gravité du palier supérieur recommandé |
| `suggestedNextCode` | string? | Code du palier supérieur (`null` si déjà au max) |
| `suggestedNextLabel` | string? | Libellé du palier supérieur |
| `reasons` | string[] | Messages de la règle (à afficher tels quels) |

---

### 6.18 Activité (audit)

`GET /api/activities` — journal des actions utilisateur (async via Messenger).

Filtres : `user`, `activity`, `ressourceName`, `ressourceIdentifier`.

---

## 7. Timeline employé (Journey)

**Lecture seule** — alimentée automatiquement par les domain events.

```http
GET /api/employees/{employeeId}/journey?order[occurredAt]=desc
```

Chaque entrée :

| Champ | Description |
|-------|-------------|
| `stage` | `ONBOARDING`, `ACTIVE`, `PROMOTION`, `TRANSFER`, `OFFBOARDING`, `RETIREMENT`, `ARCHIVED`, … |
| `eventType` | `CREATED`, `PROMOTED`, `SKILL_VALIDATED`, `ONBOARDING_COMPLETED`, `RETIRED`, … |
| `sourceEntityType` / `sourceEntityId` | Lien vers la ressource source |
| `metadata` | JSON contextuel |
| `occurredAt` | Horodatage |
| `actorId` | Utilisateur à l'origine |

**Ne jamais POST sur journey** — afficher en timeline verticale sur la fiche employé.

---

## 8. Pièges connus

### 8.1 Documents — Content-Type

- ❌ `Content-Type: application/json` sur `POST /documents` → **415**
- ✅ `FormData` sans header Content-Type manuel
- Collection Bruno : si `collection.bru` force `application/json` globalement, retirer ce header ou ajouter `~Content-Type` sur la requête document

### 8.2 Actions workflow vs PATCH

Ne pas tenter `PATCH { status: "APPROVED" }` sur les workflows — utiliser les routes `/approvals`, `/rejections`, etc.

### 8.3 Onboarding sans create

Ne pas afficher « Créer onboarding » — vérifier `GET onboarding_processes?employee=` après embauche.

### 8.4 Sécurité partiellement désactivée

Ces endpoints ont des `security` **commentées** dans le code (accès JWT seul en pratique) :

- `Document` GET/POST
- `Application` GET/POST
- `JobOffer` GET
- `Department` GET

Prévoir réactivation future des rôles `ROLE_DOC_*`, etc.

### 8.5 Permission manquante

`RecruitmentRequest` PATCH référence `ROLE_RECRUITMENT_REQUEST_UPDATE` absent de `permissions.php` — à corriger côté backend si 403 inattendu.

### 8.6 PATCH merge

Toujours `Content-Type: application/merge-patch+json` pour PATCH.

### 8.7 IDs relationnels

Les DTOs attendent des IDs (`employeeId`, `leaveRequestId`, `processId`, `taskId`) — pas des objets imbriqués.

---

## 9. Référence des préfixes ID

| Préfixe | Entité |
|---------|--------|
| US | User |
| PR | Profile |
| EM | Employee |
| WE | WorkExperience |
| DC | Document |
| CT | Contract |
| LR | LeaveRequest |
| DP | Department |
| JF | JobFamily |
| GR | Grade |
| JR | JobRole |
| CP | CareerPath |
| PO | Position |
| SKC | SkillCategory |
| SK | Skill |
| ES | EmployeeSkill |
| JRS | JobRoleRequiredSkill |
| RR | RecruitmentRequest |
| JO | JobOffer |
| AP | Application |
| OP | OnboardingProcess |
| OT | OnboardingTask |
| EC | EvaluationCycle |
| PV | PerformanceReview |
| OB | Objective |
| TC | TrainingCatalog |
| JRT | JobRoleRequiredTraining |
| TR | TrainingRequest |
| TS | TrainingSession |
| TE | TrainingEnrollment |
| PL | CareerPlan |
| MB | MobilityRequest |
| CH | CompensationHistory |
| BF | Benefit |
| EB | EmployeeBenefit |
| EP | ExitProcess |
| XT | ExitTask |
| SP | SuccessionPlan |
| SS | SanctionScale |
| DS | DisciplinaryCase |
| EJ | EmployeeJourneyEntry |

---

## 10. Annexes

### 10.1 Architecture backend (rappel)

```
HTTP → Dto → State Processor → Model → Manager → Entity → Domain Event → Journey
```

Détail : `ARCHITECTURE.md` à la racine du projet.

### 10.2 Smoke tests

Sans HTTP :

```bash
php bin/console ar:smoke:test
```

Collection Postman HTTP : `docs/postman/arca-smoke.postman_collection.json`

### 10.3 Worker async (paie)

```bash
php bin/console messenger:consume async -vv
```

### 10.4 Tests automatisés

```bash
./vendor/bin/phpunit --testsuite Unit
./vendor/bin/phpunit --testsuite Functional
```

### 10.5 Client HTTP type (Axios)

```typescript
const api = axios.create({
  baseURL: '/api',
  headers: { Accept: 'application/json' },
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

// PATCH
api.patch('/employees/EMxxx', { phone: '…' }, {
  headers: { 'Content-Type': 'application/merge-patch+json' },
});

// Upload document
const form = new FormData();
form.append('file', file);
form.append('type', 'CV');
form.append('holderId', employeeId);
form.append('holderType', 'EMPLOYEE');
form.append('title', 'CV');
await api.post('/documents', form); // pas de Content-Type manuel
```

### 10.6 Scénario bout-en-bout (recrutement → actif)

```
1. POST /recruitment_requests
2. POST /recruitment_requests/approvals
3. POST /job_offers → POST /job_offers/publications
4. POST /applications
5. POST /applications/hirings
6. GET /onboarding_processes?employee={newEmId}
7. GET /onboarding_tasks?process={opId}
8. POST /onboarding_tasks/starts + /completions (× N)
9. (auto ou POST /onboarding_processes/completions)
10. GET /employees/{id} → status ACTIVE
11. GET /employees/{id}/journey → timeline complète
```

---

*Document généré pour Arca — phases 0 à 10. Pour toute route exacte, se référer aux attributs `#[ApiResource]` dans `src/Entity/` et `src/Model/`, ou à la doc OpenAPI : `/api/docs`.*
