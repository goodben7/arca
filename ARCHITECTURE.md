# Arca — Architecture

Arca est un SIRH exposé en API REST (Symfony 8, API Platform 4, Doctrine ORM, JWT).

Chaque action métier suit le même flux :

```
HTTP Request
    → Dto (input API)
    → State Processor (adaptateur API Platform)
    → Model (value object interne)
    → Manager (logique métier)
    → Entity (persistance)
    → Event (audit & effets transverses)
```

---

## Couches

### 1. Dto (`src/Dto/`)

Contrat d'entrée de l'API. Validé par Symfony Validator. Ne contient pas de logique métier.

Exemple : `ActivateEmployeeDto` avec `employeeId`.

### 2. State Processor (`src/State/`)

Implémente `ProcessorInterface` (API Platform). Rôle unique : **mapper le Dto vers le Manager**.

- Pas de logique métier
- Pas d'accès direct à Doctrine
- Une action = un Processor

```php
// ActivateEmployeeProcessor
return $this->manager->activateFrom(new ActivateEmployeeModel($data->employeeId));
```

Les opérations sont déclarées sur l'entité via `#[ApiResource]` :

```php
new Post(
    uriTemplate: '/employees/activations',
    input: ActivateEmployeeDto::class,
    processor: ActivateEmployeeProcessor::class,
    security: 'is_granted("ROLE_EMPLOYEE_ACTIVATE")',
)
```

### 3. Model (`src/Model/`)

Value objects internes passés au Manager (`ActivateEmployeeModel`, `NewEmployeeModel`…).

Les classes `*Constants` définissent les **machines à états** :

- statuts (`STATUS_ACTIVE`, `STATUS_PENDING`…)
- actions (`ACTION_ACTIVATE`, `ACTION_END`…)
- `getAllowedActionsForStatus()` — transitions autorisées

### 4. Manager (`src/Manager/`)

Cœur métier. Responsabilités :

1. Charger l'entité
2. Vérifier l'éligibilité (`assertActionAllowed`)
3. Appliquer la transition (`applyXxxAction`)
4. Persister (`flush`)
5. Déclencher l'audit (`ActivityEventDispatcher`)

Un Manager par agrégat : `EmployeeManager`, `ContractManager`, etc.

### 5. Entity (`src/Entity/`)

Modèle de persistance Doctrine + configuration API Platform (opérations, filtres, groupes de sérialisation).

- IDs custom via `ID_PREFIX` + `IdGenerator` (format `EM` + lettres + timestamp)
- Champs d'audit : `activatedAt`, `activatedBy`, etc.
- Implémente `RessourceInterface`

### 6. Event (`src/Event/` + `src/Service/ActivityEventDispatcher`)

Après chaque action significative, le Manager appelle :

```php
$this->eventDispatcher->dispatch($employee, ActivityEvent::ACTION_EDIT, null, 'employee activated');
```

`ActivityEventDispatcher` :
- dispatch un `ActivityEvent` Symfony (listeners possibles)
- envoie un `UserActivityLoggedMessage` via Messenger (journal async)

### 7. Domain Event (`src/Event/Domain/` + `src/EventListener/`)

Les effets transverses (timeline employé, notifications futures) passent par des **événements domaine** :

```php
$this->domainEventDispatcher->dispatch(
    new EmployeeActivatedEvent($employee, $this->resolveActorId(), $previousStatus)
);
```

`RecordEmployeeJourneyListener` écoute ces événements et appelle `EmployeeJourneyRecorder`. Les Managers ne connaissent pas Journey directement.

### 8. Policy (`src/Policy/`)

Contrat d'éligibilité métier (promotion, formation…) :

- `EligibilityPolicyInterface` — une policy par règle métier
- `PolicyResult` — `eligible` + `reasons[]`
- `PolicyEvaluator` — agrège les policies taggées `app.eligibility_policy`

---

## Transverse

| Élément | Rôle |
|---------|------|
| `config/permissions.php` | Catalogue des rôles (`ROLE_EMPLOYEE_ACTIVATE`…) |
| `config/ressources.php` | Registre des entités et préfixes ID |
| `src/Message/Command/` | CQRS — actions intentionnelles (ex. `CreateUserCommand`) |
| `src/Message/Query/` | CQRS — lectures (ex. `GetUserDetails`) |
| `src/Exception/` | `InvalidActionInputException`, `UnavailableDataException` |

---

## Ajouter une nouvelle action métier

1. `Model/XxxConstants.php` — statut + action + transitions
2. `Dto/XxxActionDto.php` — input API
3. `Model/XxxActionModel.php` — value object
4. `Manager/XxxManager.php` — méthode `actionFrom()`
5. `State/XxxActionProcessor.php` — adaptateur API Platform
6. `Entity/Xxx.php` — opération `#[ApiResource]`
7. `config/permissions.php` — rôle associé
8. Test unitaire Manager dans `tests/Unit/Manager/`

---

## Tests

Les Managers se testent en **unitaire** (mocks Doctrine / Security / Bus), sans base de données.

```bash
./bin/phpunit tests/Unit/Manager/
```

---

## Modèle Job Architecture

Deux concepts de « poste » coexistent dans Arca :

| Concept | Entité | Rôle | Exemple |
|---------|--------|------|---------|
| **Slot organisationnel** | `Position` (PO) | Poste budgétaire avec effectif (`headcount`, `openPositions`), cycle open/close | « 2 postes Dev Senior ouverts en IT » |
| **Fiche métier** | `JobRole` (JR) | Référentiel RH : titre, famille (`JobFamily`), grade (`Grade`) | « Comptable Junior », « Directeur Financier » |

### Relations clés

```
JobFamily → JobRole → Grade
CareerPath: JobRole → JobRole (conditions JSON)
Employee.jobRole (FK) + Employee.grade (FK)
Employee.position = ID du slot PO (string, legacy) — à migrer vers FK plus tard
Employee.department = string (legacy) — FK Department prévu ultérieurement
```

### Recrutement

`RecruitmentRequest` et `JobOffer` référencent `JobRole`. À l'embauche, `jobRole` et `grade` sont propagés sur l'`Employee`.

### Seed de démonstration

```bash
php bin/console app:seed:job-architecture
php bin/console app:seed:skills
```

`app:seed:job-architecture` — familles FIN / IT / RH, grades G1–G5, filière comptable (ACC-JUNIOR → CFO) et `CareerPath`. Aborte si le référentiel emplois n'est pas vide.

`app:seed:skills` — catégorie `COMPTA`, 8 compétences catalogue et 17 `JobRoleRequiredSkill` pour la filière compta. Nécessite `app:seed:job-architecture`. Aborte si déjà seedé.

### Journey

Lors de la création d'un employé avec `jobRole`, le listener enregistre `JOB_ROLE_ASSIGNED` (en plus de `CREATED`).

---

## Phase 1 — Livrable Job Architecture

| Élément | Statut |
|---------|--------|
| Référentiel `JobFamily` / `Grade` / `JobRole` / `CareerPath` | OK |
| `Employee.jobRole` + `Employee.grade` (FK) | OK |
| Recrutement (`RecruitmentRequest`, `JobOffer`) lié au référentiel | OK |
| Journey `JOB_ROLE_ASSIGNED` à la création | OK |
| Commande `app:seed:job-architecture` | OK |

---

## Phase 2 — Compétences

| Concept | Entité | Rôle |
|---------|--------|------|
| **Catégorie** | `SkillCategory` (SKC) | Regroupement du catalogue |
| **Compétence catalogue** | `Skill` (SK) | Référentiel : code, nom, catégorie, description |
| **Compétence employé** | `EmployeeSkill` (ES) | Lien employé → skill + niveau + `validatedAt` |
| **Compétence requise** | `JobRoleRequiredSkill` (JRS) | Exigence métier : jobRole + skill + `minimumLevel` |

### API assignation employé

- `POST /api/employee_skills` — assigner une compétence catalogue à un employé
- `POST /api/employee_skills/validations` — valider une compétence employé
- `PATCH /api/employee_skills/{id}` — mise à jour niveau (déclenche `SKILL_LEVEL_UPGRADED` si progression)

### Journey

- `SKILL_VALIDATED` — validation explicite ou via patch `validatedAt`
- `SKILL_LEVEL_UPGRADED` — montée de niveau (BEGINNER → … → EXPERT)

---

## Phase 2 — Livrable

| Élément | Statut |
|---------|--------|
| Référentiel catalogue séparé des compétences employé | OK |
| `JobRoleRequiredSkill` (jobRole + skill + minimumLevel) | OK |
| Migration données legacy → catalogue `LIBRE` | OK |
| API assignation + validation employé | OK |
| Journey `SKILL_VALIDATED` / `SKILL_LEVEL_UPGRADED` | OK |
| Commande `app:seed:skills` | OK |

---

## Phase 3 — Onboarding

| Concept | Entité | Rôle |
|---------|--------|------|
| **Processus** | `OnboardingProcess` (OP) | Par employé : statut, `startedAt`, `completedAt` |
| **Tâche** | `OnboardingTask` (OT) | Checklist : titre, type, statut, `assignedTo`, `dueDate` |

Types de tâches : `DOCUMENT`, `IT_ACCESS`, `TRAINING`, `EQUIPMENT`, `HR_FORM`.

Workflow tâche / processus : `PENDING` → `IN_PROGRESS` → `COMPLETED` | `CANCELLED`.

### Déclenchement automatique

- À `EmployeeCreatedEvent` (embauche via `ApplicationHiredEvent` → `createFrom`, ou création directe) : `StartOnboardingOnEmployeeCreatedListener` lance un processus avec la checklist `config/onboarding_checklist.php`.
- Nouvel employé créé en statut `INACTIVE` ; clôture onboarding → `OnboardingCompletedEvent` → activation automatique si toujours inactif.

### API

- `GET /api/onboarding_processes`, `GET /api/onboarding_processes/{id}`
- `POST /api/onboarding_processes/completions`, `POST /api/onboarding_processes/cancellations`
- `GET /api/onboarding_tasks`, `GET /api/onboarding_tasks/{id}`
- `POST /api/onboarding_tasks/starts`, `POST /api/onboarding_tasks/completions`, `POST /api/onboarding_tasks/cancellations`

### Journey

- `ONBOARDING_STARTED` — création du processus
- `ONBOARDING_COMPLETED` — clôture (manuelle ou auto quand toutes les tâches sont terminées/annulées)

---

## Phase 3 — Livrable

| Élément | Statut |
|---------|--------|
| Process onboarding auto à l'embauche | OK |
| Checklist configurable (`config/onboarding_checklist.php`) | OK |
| Journey `ONBOARDING_STARTED` / `ONBOARDING_COMPLETED` | OK |
| Employé `INACTIVE` à la création, activation à la fin onboarding | OK |

---

## Phase 4 — Performance

| Concept | Entité | Rôle |
|---------|--------|------|
| **Cycle d'évaluation** | `EvaluationCycle` (EC) | Période : `DRAFT` → `OPEN` → `CLOSED` |
| **Évaluation** | `PerformanceReview` (PV) | Par employé + cycle : score, `DRAFT` → `SUBMITTED` → `VALIDATED` |
| **Objectif SMART** | `Objective` (OB) | Lié employé + cycle : specific, measurable, targetValue, dueDate… |

### API Performance

- `POST /api/evaluation_cycles`, `PATCH`, `POST /evaluation_cycles/opens`, `POST /evaluation_cycles/closures`
- `POST /api/performance_reviews`, `PATCH`, `POST /performance_reviews/submissions`, `POST /performance_reviews/validations`
- `POST /api/objectives`, `PATCH`, `POST /objectives/activations`, `completions`, `cancellations`
- `GET /api/employees/{employeeId}/promotion-eligibility?targetJobRole=JRxxx`

### Policy & Workflow

- `PromotionEligibilityPolicy` — career path + ancienneté + score perf + compétences requises (`JobRoleRequiredSkill`)
- `PolicyEvaluator` branché sur l'endpoint promotion-eligibility
- Workflow lite : `ApprovalWorkflowInterface`, `SimpleSequentialWorkflow`, `LeaveRequestApprovalWorkflow` (refactor `LeaveRequestManager`)

---

## Phase 4 — Livrable

| Élément | Statut |
|---------|--------|
| Cycle d'évaluation fonctionnel | OK |
| Objectifs SMART | OK |
| 1ère policy d'éligibilité promotion | OK |
| Workflow lite sur LeaveRequest (refactor) | OK |

---

## Phase 5 — Formation

| Concept | Entité | Rôle |
|---------|--------|------|
| **Catalogue** | `TrainingCatalog` (TC) | Référentiel : title, description, provider, duration, cost |
| **Formation requise** | `JobRoleRequiredTraining` (JRT) | Exigence métier : jobRole + catalogItem |
| **Session** (enrichie) | `TrainingSession` | Lien optionnel `catalogItem` (FK) |
| **Inscription** (enrichie) | `TrainingEnrollment` | score, certificate, workflow étendu |

Workflow inscription : `ASSIGNED` → `IN_PROGRESS` → `COMPLETED` → `CERTIFIED` (+ `ABSENT`).

### API

- `POST/PATCH /api/training_catalogs` — catalogue
- `POST/PATCH /api/job_role_required_trainings` — formations requises par fiche métier
- `POST /api/training_sessions` — `catalogItem` optionnel (TC...)
- `POST /api/training_enrollments/starts`, `/completions`, `/certifications`

### Policy

- `TrainingRequiredForPromotionPolicy` — formations certifiées requises (`JobRoleRequiredTraining` + `CareerPath.requiredTrainings`)

### Journey

- `TRAINING_COMPLETED` — fin de formation
- `TRAINING_CERTIFIED` — certification avec score + certificat

---

## Phase 5 — Livrable

| Élément | Statut |
|---------|--------|
| Catalogue formations | OK |
| Sessions liées au catalogue | OK |
| Certificats + score | OK |
| Journey `TRAINING_COMPLETED` / `TRAINING_CERTIFIED` | OK |

---

## Phase 6 — Carrière & Mobilité

| Concept | Entité | Rôle |
|---------|--------|------|
| **Plan de carrière** | `CareerPlan` (PL) | Intention long terme : employee → targetJobRole, targetGrade, targetDate |
| **Demande de mobilité** | `MobilityRequest` (MB) | Types : TRANSFER, PROMOTION, DEMOTION, SECONDMENT |

Workflow (`MobilityApprovalWorkflow` / `SimpleSequentialWorkflow`) :

```
DRAFT → MANAGER_APPROVAL → HR_APPROVAL → EXECUTIVE_APPROVAL → IMPLEMENTED
```

États terminaux : `REJECTED`, `CANCELLED`.

### API

- `POST/PATCH /api/career_plans` — plan de carrière (statuts ACTIVE, ACHIEVED, CANCELLED)
- `POST /api/mobility_requests` — création en brouillon
- `POST /api/mobility_requests/submissions` — soumission (policy si PROMOTION)
- `POST /api/mobility_requests/approvals` — approbation palier courant
- `POST /api/mobility_requests/rejections` — rejet
- `POST /api/mobility_requests/cancellations` — annulation (DRAFT uniquement)

### Policy

- `PromotionEligibilityPolicy` + `TrainingRequiredForPromotionPolicy` via `PolicyEvaluator` **avant soumission** pour type `PROMOTION`

### Domain Events

- `MobilityImplementedEvent` (à `IMPLEMENTED`) →
  - `ApplyMobilityOnImplementedListener` : met à jour `Employee.jobRole`, `grade`, `department`
  - `RecordEmployeeJourneyListener` : `PROMOTED` (PROMOTION/DEMOTION) ou `TRANSFERRED` (TRANSFER/SECONDMENT)

`CompensationHistory` — reporté Phase 7.

---

## Phase 6 — Livrable

| Élément | Statut |
|---------|--------|
| Demandes de mobilité/promotion | OK |
| Workflow d'approbation réutilisable | OK |
| Mise à jour auto Employee à l'implémentation | OK |
| Éligibilité promotion calculée à la soumission | OK |
| Plans de carrière (CareerPlan) | OK |

---

## Phase 7 — Compensation

Pas de `SalaryGrid` pour l'instant.

| Concept | Fichier / entité | Rôle |
|---------|------------------|------|
| **Policy** | `CompensationPolicyInterface` | Contrat de calcul du nouveau salaire |
| **Stratégie grade** | `GradeBasedCompensationPolicy` | `newSalary = grade.rank × base_salary_per_rank` (`config/compensation.php`) |
| **Historique** | `CompensationHistory` (CH) | `employee`, `oldSalary`, `newSalary`, `effectiveDate`, `reason`, `sourceEvent` |

### Déclencheurs

- **`MobilityImplementedEvent`** (promotion/demotion) → `RecordCompensationOnMobilityListener` (après `ApplyMobilityOnImplementedListener`)
- **Manuel** : `POST /api/compensation_histories/recordings`

À chaque enregistrement : mise à jour du salaire du **contrat actif** + `NotifyPayrollMessage` (Messenger **async**).

### API

- `GET /api/compensation_histories` — liste
- `GET /api/compensation_histories/{id}` — détail
- `POST /api/compensation_histories/recordings` — saisie manuelle

`sourceEvent` : `MOBILITY_IMPLEMENTED` | `MANUAL`

---

## Phase 7 — Livrable

| Élément | Statut |
|---------|--------|
| Historique salarial (`CompensationHistory`) | OK |
| 1 stratégie de calcul (grade-based) | OK |
| Event async `NotifyPayrollMessage` (Messenger) | OK |
| Déclenchement auto sur mobilité implémentée | OK |

---

## Phase 8 — Benefits + Offboarding

### Avantages employé (CRUD simple)

| Concept | Préfixe | Rôle |
|---------|---------|------|
| **Benefit** | BF | Catalogue d'avantages (`code`, `name`, `type`, `description`) |
| **EmployeeBenefit** | EB | Inscription employé (`employee`, `benefit`, `startDate`, `endDate`, `status`) |

Types d'avantage : `HEALTH`, `TRANSPORT`, `MEAL`, `OTHER` (`BenefitConstants`).

API :

- `GET/POST/PATCH /api/benefits`
- `GET/POST/PATCH /api/employee_benefits` — création via `CreateEmployeeBenefitProcessor`

À la clôture du process de sortie, les inscriptions actives sont terminées (`EmployeeBenefitManager::endActiveBenefitsForEmployee`).

### Process de sortie (`ExitProcess` + `ExitTask`)

| Concept | Préfixe | Rôle |
|---------|---------|------|
| **ExitProcess** | EP | `reason`, `departureDate`, `status`, checklist |
| **ExitTask** | XT | Tâches calquées sur `OnboardingTask` |

Motifs (`ExitProcessConstants`) : `RESIGNATION`, `DISMISSAL`, `RETIREMENT`, `END_OF_CONTRACT`.

Statuts process : `PENDING` → `IN_PROGRESS` → `COMPLETED` (ou `CANCELLED`).

Checklist par défaut : `config/offboarding_checklist.php` → `OffboardingChecklistProvider` (knowledge transfer, exit interview, équipement, accès, formalités RH).

### Flux offboarding

```
POST /api/exit_processes                    → PENDING
POST /api/exit_processes/starts             → IN_PROGRESS + création ExitTask
POST /api/exit_tasks/starts|completions     → avancement checklist
POST /api/exit_processes/completions        → orchestration finale
```

À **COMPLETED** (`ExitProcessManager::completeFrom`) :

1. Vérification que toutes les tâches sont terminées
2. `employee.departureDate` = date de départ du process
3. `ContractManager::endFrom` (contrat actif)
4. `EmployeeManager::retireFrom` si `RETIREMENT`, sinon `terminateFrom`
5. `UserManager::lockUser` si l'employé a un compte utilisateur
6. Clôture des `EmployeeBenefit` actifs
7. `ExitProcessCompletedEvent`

### Journey

| Événement domaine | Stage | Event type |
|-------------------|-------|------------|
| `ExitProcessStartedEvent` | `OFFBOARDING` | `OFFBOARDING_STARTED` |
| `ExitProcessCompletedEvent` | `ARCHIVED` | `ARCHIVED` |

`EmployeeTerminatedEvent` continue d'enregistrer `TERMINATED` ; `ARCHIVED` est distinct à la fin du process.

### API offboarding

- `GET/POST/PATCH /api/exit_processes`
- `POST /api/exit_processes/starts`
- `POST /api/exit_processes/completions`
- `POST /api/exit_processes/cancellations`
- `GET/POST/PATCH /api/exit_tasks`
- `POST /api/exit_tasks/starts` | `completions` | `cancellations`

---

## Phase 8 — Livrable

| Élément | Statut |
|---------|--------|
| Catalogue + inscriptions avantages (`Benefit`, `EmployeeBenefit`) | OK |
| Process sortie structuré (`ExitProcess`, `ExitTask`, checklist) | OK |
| Orchestration terminate/retire + fin contrat + lock user | OK |
| Journey `OFFBOARDING_STARTED`, `ARCHIVED` | OK |
| Migration `Version20260703120000` | OK |

---

## Phase 9–10 — Succession + Dashboard RH

### Plans de succession

| Concept | Préfixe | Rôle |
|---------|---------|------|
| **SuccessionPlan** | SP | `criticalJobRole`, `candidate`, `readinessLevel`, `status` |

Niveaux de maturité (`SuccessionPlanConstants`) : `READY_NOW`, `WITHIN_1_YEAR`, `WITHIN_2_YEARS`, `DEVELOPMENT_NEEDED`.

Postes critiques configurés dans `config/succession.php` (codes `CFO`, `ACC-LEAD`, `ACC-SR`).

API :

- `GET/POST/PATCH /api/succession_plans`
- Création via `CreateSuccessionPlanProcessor` — valide employé actif + rôle critique + unicité (rôle × candidat)

### Dashboard RH (lecture seule)

`GET /api/hr/dashboard` — KPIs calculés à la volée (`HrDashboardCalculator`) :

| KPI | Source |
|-----|--------|
| **effectif** | employés `ACTIVE` |
| **turnover** | départs (`TERMINATED`/`RETIRED`) sur 12 mois + taux % |
| **promotions** | mobilités `PROMOTION` `IMPLEMENTED` sur 12 mois |
| **formations** | inscriptions `IN_PROGRESS` + certifications sur 12 mois |
| **succession coverage** | rôles critiques couverts / total (`config/succession.php`) |
| **compétences critiques** | écarts skill vs `JobRoleRequiredSkill` sur postes critiques |

Permission : `ROLE_HR_DASHBOARD_VIEW`.

---

## Phase 9–10 — Livrable

| Élément | Statut |
|---------|--------|
| `SuccessionPlan` (CRUD + rôles critiques) | OK |
| `GET /api/hr/dashboard` (KPIs temps réel) | OK |
| Migration `Version20260704120000` | OK |
| Smoke test scénario 7 | OK |

---

## Smoke tests (validation avant suite)

Commande Symfony (recommandée, sans serveur HTTP ni JWT) :

```bash
# Prérequis une fois
php bin/console app:seed:job-architecture
php bin/console app:seed:skills
php bin/console ar:seed:profiles

# Lancer les 7 scénarios
php bin/console ar:smoke:test

# Options
php bin/console ar:smoke:test --keep-data    # conserver les données SMOKE-*
php bin/console ar:smoke:test --fail-fast    # arrêt au premier échec
```

Scénarios couverts :

| # | Scénario | Vérifie |
|---|----------|---------|
| 1 | Mobilité TRANSFER (workflow complet) | Employee.department, journey `TRANSFERRED` |
| 2 | Mobilité PROMOTION (workflow complet) | Employee jobRole/grade, journey `PROMOTED`, `CompensationHistory`, contrat |
| 3 | Éligibilité promotion | `PolicyEvaluator` / policies |
| 4 | Compensation manuelle | `POST` équivalent via Manager, contrat mis à jour |
| 5 | `NotifyPayrollMessage` | Handler Messenger (async documenté) |
| 6 | Offboarding complet | `ExitProcess` + checklist + `EmployeeBenefit` clôturé, journey `OFFBOARDING_STARTED` + `ARCHIVED` |
| 7 | Succession + dashboard | `SuccessionPlan` sur rôle critique + KPIs `GET /api/hr/dashboard` |

Collection Postman équivalente (API HTTP) : `docs/postman/arca-smoke.postman_collection.json`

Worker async (après changements de salaire en conditions réelles) :

```bash
php bin/console messenger:consume async -vv
```

---

## Évolutions en cours

- `EmployeeJourneyEntry` — timeline métier branchée via Domain Events + `RecordEmployeeJourneyListener`

---

## Phase 11 — Module Sanctions

### Phase A — Échelle des sanctions (référentiel)

| Concept | Préfixe | Rôle |
|---------|---------|------|
| **SanctionScale** | SS | Catalogue disciplinaire (`code`, `label`, `severityLevel`, `requiresHearing`, `maxDurationDays`, `active`) |

Codes seedés : `WARN` (1), `BLAME` (2), `SUSPEND` (3), `DISMISS` (4).

API :

- `GET/POST/PATCH /api/sanction_scales`
- Permissions : `ROLE_SANCTION_SCALE_*`
- Seed : `php bin/console app:seed:sanction-scales`
- Migration : `Version20260729120000`

### Phase B — Affaire disciplinaire (workflow)

| Concept | Préfixe | Rôle |
|---------|---------|------|
| **DisciplinaryCase** | DS | Procédure disciplinaire (machine à états, POST dédiés) |

Statuts : `DRAFT` → `OPENED` → `HEARING_SCHEDULED` → `DECISION_PENDING` → `SANCTION_APPLIED` → `CLOSED` (↘ `CANCELLED` / `REJECTED`).

Si `requiresHearing=false`, skip hearing : `OPENED` → decide.

API :

- `POST /api/disciplinary_cases` (+ openings, hearings, decisions, applications, cancellations, rejections, closures)
- Permissions : `ROLE_DISCIPLINARY_CASE_*`
- Journey : `DISCIPLINARY_STARTED`, `SANCTION_APPLIED` (stage `DISCIPLINARY`)
- Effet : code `SUSPEND` → suspension employé à l’application
- Migration : `Version20260729140000`

### Phase C — Effets d’application et récidives

| Concept | Rôle |
|---------|------|
| **Document** (WARN/BLAME) | Métadonnée `TYPE_WARNING_LETTER` liée à l’affaire (`DS_DOCUMENT`) |
| **ExitProcess** (DISMISS) | Process de sortie `REASON_DISMISSAL` créé à l’application (`DS_EXIT_PROCESS`) |
| **DisciplinarySummaryResult** | Synthèse récidives par employé |

Effets à l’application (`applications`) :

- `WARN` / `BLAME` → `Document` `TYPE_WARNING_LETTER` (fichier optionnel via multipart `file`)
- `SUSPEND` → suspension employé (Phase B)
- `DISMISS` → `ExitProcess` créé **et démarré** (`IN_PROGRESS`, checklist offboarding)

Règles :

- Création refusée si affaire active existante pour l’employé
- `GET /api/employees/{employeeId}/disciplinary-summary` — compteur sanctions, gravité max, dernière sanction, `hasActiveCase`, `isRepeatOffender`

Migration : `Version20260729160000`
