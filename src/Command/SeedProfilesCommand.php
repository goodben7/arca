<?php

namespace App\Command;

use App\Entity\Profile;
use App\Manager\PermissionManager;
use App\Model\UserProxyIntertace;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'ar:seed:profiles',
    description: 'Seed profiles',
)]
class SeedProfilesCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pm = PermissionManager::getInstance();
        $all = array_map(fn($p) => $p->getPermissionId(), $pm->getPermissions());

        $userAdmin = [
            'ROLE_USER_CREATE',
            'ROLE_USER_LOCK',
            'ROLE_USER_CHANGE_PWD',
            'ROLE_USER_DETAILS',
            'ROLE_USER_LIST',
            'ROLE_USER_EDIT',
            'ROLE_USER_DELETE',
            'ROLE_USER_SET_PROFILE',
        ];
        $userSelf = ['ROLE_USER_CHANGE_PWD', 'ROLE_USER_DETAILS'];

        $profileAdmin = [
            'ROLE_PROFILE_CREATE',
            'ROLE_PROFILE_LIST',
            'ROLE_PROFILE_UPDATE',
            'ROLE_PROFILE_DETAILS',
        ];
        $profileRead = ['ROLE_PROFILE_LIST', 'ROLE_PROFILE_DETAILS'];

        $activityRead = ['ROLE_ACTIVITY_LIST', 'ROLE_ACTIVITY_VIEW'];

        $employeeCrud = [
            'ROLE_EMPLOYEE_CREATE',
            'ROLE_EMPLOYEE_LIST',
            'ROLE_EMPLOYEE_DETAILS',
            'ROLE_EMPLOYEE_UPDATE',
            'ROLE_EMPLOYEE_ACTIVATE',
            'ROLE_EMPLOYEE_DEACTIVATE',
            'ROLE_EMPLOYEE_SET_ON_LEAVE',
            'ROLE_EMPLOYEE_SUSPEND',
            'ROLE_EMPLOYEE_TERMINATE',
            'ROLE_EMPLOYEE_RETIRE',
            'ROLE_EMPLOYEE_SET_PROBATION',
            'ROLE_EMPLOYEE_ASSIGN_MANAGER',
        ];
        $employeeRead = ['ROLE_EMPLOYEE_LIST', 'ROLE_EMPLOYEE_DETAILS'];
        $employeeUpdate = ['ROLE_EMPLOYEE_DETAILS', 'ROLE_EMPLOYEE_UPDATE'];

        $workExperienceCrud = [
            'ROLE_WORK_EXPERIENCE_CREATE',
            'ROLE_WORK_EXPERIENCE_LIST',
            'ROLE_WORK_EXPERIENCE_DETAILS',
            'ROLE_WORK_EXPERIENCE_UPDATE',
        ];
        $workExperienceRead = ['ROLE_WORK_EXPERIENCE_LIST', 'ROLE_WORK_EXPERIENCE_DETAILS'];

        $skillCategoryCrud = [
            'ROLE_SKILL_CATEGORY_CREATE',
            'ROLE_SKILL_CATEGORY_LIST',
            'ROLE_SKILL_CATEGORY_DETAILS',
            'ROLE_SKILL_CATEGORY_UPDATE',
        ];
        $skillCategoryRead = ['ROLE_SKILL_CATEGORY_LIST', 'ROLE_SKILL_CATEGORY_DETAILS'];

        $skillCrud = [
            'ROLE_SKILL_CREATE',
            'ROLE_SKILL_LIST',
            'ROLE_SKILL_DETAILS',
            'ROLE_SKILL_UPDATE',
        ];
        $skillRead = ['ROLE_SKILL_LIST', 'ROLE_SKILL_DETAILS'];

        $employeeSkillCrud = [
            'ROLE_EMPLOYEE_SKILL_CREATE',
            'ROLE_EMPLOYEE_SKILL_LIST',
            'ROLE_EMPLOYEE_SKILL_DETAILS',
            'ROLE_EMPLOYEE_SKILL_UPDATE',
        ];
        $employeeSkillRead = ['ROLE_EMPLOYEE_SKILL_LIST', 'ROLE_EMPLOYEE_SKILL_DETAILS'];

        $jobRoleRequiredSkillCrud = [
            'ROLE_JOB_ROLE_REQUIRED_SKILL_CREATE',
            'ROLE_JOB_ROLE_REQUIRED_SKILL_LIST',
            'ROLE_JOB_ROLE_REQUIRED_SKILL_DETAILS',
            'ROLE_JOB_ROLE_REQUIRED_SKILL_UPDATE',
        ];
        $jobRoleRequiredSkillRead = ['ROLE_JOB_ROLE_REQUIRED_SKILL_LIST', 'ROLE_JOB_ROLE_REQUIRED_SKILL_DETAILS'];

        $docCrud = [
            'ROLE_DOC_CREATE',
            'ROLE_DOC_LIST',
            'ROLE_DOC_DETAILS',
            'ROLE_DOC_DELETE',
        ];
        $docRead = ['ROLE_DOC_LIST', 'ROLE_DOC_DETAILS'];

        $departmentCrud = [
            'ROLE_DEPARTMENT_CREATE',
            'ROLE_DEPARTMENT_LIST',
            'ROLE_DEPARTMENT_DETAILS',
            'ROLE_DEPARTMENT_UPDATE',
        ];
        $departmentRead = ['ROLE_DEPARTMENT_LIST', 'ROLE_DEPARTMENT_DETAILS'];

        $contractCrud = [
            'ROLE_CONTRACT_CREATE',
            'ROLE_CONTRACT_LIST',
            'ROLE_CONTRACT_DETAILS',
            'ROLE_CONTRACT_UPDATE',
            'ROLE_CONTRACT_ACTIVATE',
            'ROLE_CONTRACT_END',
            'ROLE_CONTRACT_CANCEL',
            'ROLE_CONTRACT_SET_PENDING',
        ];
        $contractRead = ['ROLE_CONTRACT_LIST', 'ROLE_CONTRACT_DETAILS'];

        $leaveRequestCrud = [
            'ROLE_LEAVE_REQUEST_CREATE',
            'ROLE_LEAVE_REQUEST_LIST',
            'ROLE_LEAVE_REQUEST_DETAILS',
            'ROLE_LEAVE_REQUEST_UPDATE',
            'ROLE_LEAVE_REQUEST_APPROVE',
            'ROLE_LEAVE_REQUEST_REJECT',
        ];
        $leaveRequestRead = ['ROLE_LEAVE_REQUEST_LIST', 'ROLE_LEAVE_REQUEST_DETAILS'];
        $leaveRequestManage = [
            'ROLE_LEAVE_REQUEST_LIST',
            'ROLE_LEAVE_REQUEST_DETAILS',
            'ROLE_LEAVE_REQUEST_APPROVE',
            'ROLE_LEAVE_REQUEST_REJECT',
        ];

        $positionCrud = [
            'ROLE_POSITION_CREATE',
            'ROLE_POSITION_LIST',
            'ROLE_POSITION_DETAILS',
            'ROLE_POSITION_UPDATE',
            'ROLE_POSITION_OPEN',
            'ROLE_POSITION_CLOSE',
        ];
        $positionRead = ['ROLE_POSITION_LIST', 'ROLE_POSITION_DETAILS'];

        $recruitmentRequestCrud = [
            'ROLE_RECRUITMENT_REQUEST_CREATE',
            'ROLE_RECRUITMENT_REQUEST_LIST',
            'ROLE_RECRUITMENT_REQUEST_DETAILS',
            'ROLE_RECRUITMENT_REQUEST_UPDATE',
            'ROLE_RECRUITMENT_REQUEST_APPROVE',
            'ROLE_RECRUITMENT_REQUEST_REJECT',
        ];
        $recruitmentRequestRead = ['ROLE_RECRUITMENT_REQUEST_LIST', 'ROLE_RECRUITMENT_REQUEST_DETAILS'];

        $jobOfferCrud = [
            'ROLE_JOB_OFFER_CREATE',
            'ROLE_JOB_OFFER_LIST',
            'ROLE_JOB_OFFER_DETAILS',
            'ROLE_JOB_OFFER_UPDATE',
            'ROLE_JOB_OFFER_PUBLISH',
            'ROLE_JOB_OFFER_CLOSE',
            'ROLE_JOB_OFFER_SET_DRAFT',
        ];
        $jobOfferRead = ['ROLE_JOB_OFFER_LIST', 'ROLE_JOB_OFFER_DETAILS'];

        $applicationCrud = [
            'ROLE_APPLICATION_CREATE',
            'ROLE_APPLICATION_LIST',
            'ROLE_APPLICATION_DETAILS',
            'ROLE_APPLICATION_SET_APPLIED',
            'ROLE_APPLICATION_SHORTLIST',
            'ROLE_APPLICATION_INTERVIEW',
            'ROLE_APPLICATION_REJECT',
            'ROLE_APPLICATION_HIRE',
        ];
        $applicationRead = ['ROLE_APPLICATION_LIST', 'ROLE_APPLICATION_DETAILS'];

        $trainingRequestCrud = [
            'ROLE_TRAINING_REQUEST_CREATE',
            'ROLE_TRAINING_REQUEST_LIST',
            'ROLE_TRAINING_REQUEST_DETAILS',
            'ROLE_TRAINING_REQUEST_APPROVE',
            'ROLE_TRAINING_REQUEST_REJECT',
        ];
        $trainingRequestRead = ['ROLE_TRAINING_REQUEST_LIST', 'ROLE_TRAINING_REQUEST_DETAILS'];

        $trainingSessionCrud = [
            'ROLE_TRAINING_SESSION_CREATE',
            'ROLE_TRAINING_SESSION_LIST',
            'ROLE_TRAINING_SESSION_DETAILS',
            'ROLE_TRAINING_SESSION_UPDATE',
            'ROLE_TRAINING_SESSION_START',
            'ROLE_TRAINING_SESSION_COMPLETE',
            'ROLE_TRAINING_SESSION_CANCEL',
            'ROLE_TRAINING_SESSION_SET_PLANNED',
        ];
        $trainingSessionRead = ['ROLE_TRAINING_SESSION_LIST', 'ROLE_TRAINING_SESSION_DETAILS'];

        $trainingEnrollmentCrud = [
            'ROLE_TRAINING_ENROLLMENT_CREATE',
            'ROLE_TRAINING_ENROLLMENT_LIST',
            'ROLE_TRAINING_ENROLLMENT_DETAILS',
            'ROLE_TRAINING_ENROLLMENT_START',
            'ROLE_TRAINING_ENROLLMENT_COMPLETE',
            'ROLE_TRAINING_ENROLLMENT_CERTIFY',
            'ROLE_TRAINING_ENROLLMENT_MARK_ABSENT',
            'ROLE_TRAINING_ENROLLMENT_SET_ENROLLED',
        ];
        $trainingEnrollmentRead = ['ROLE_TRAINING_ENROLLMENT_LIST', 'ROLE_TRAINING_ENROLLMENT_DETAILS'];

        $trainingCatalogCrud = [
            'ROLE_TRAINING_CATALOG_CREATE',
            'ROLE_TRAINING_CATALOG_LIST',
            'ROLE_TRAINING_CATALOG_DETAILS',
            'ROLE_TRAINING_CATALOG_UPDATE',
        ];
        $trainingCatalogRead = ['ROLE_TRAINING_CATALOG_LIST', 'ROLE_TRAINING_CATALOG_DETAILS'];

        $jobRoleRequiredTrainingCrud = [
            'ROLE_JOB_ROLE_REQUIRED_TRAINING_CREATE',
            'ROLE_JOB_ROLE_REQUIRED_TRAINING_LIST',
            'ROLE_JOB_ROLE_REQUIRED_TRAINING_DETAILS',
            'ROLE_JOB_ROLE_REQUIRED_TRAINING_UPDATE',
        ];
        $jobRoleRequiredTrainingRead = ['ROLE_JOB_ROLE_REQUIRED_TRAINING_LIST', 'ROLE_JOB_ROLE_REQUIRED_TRAINING_DETAILS'];

        $onboardingProcessCrud = [
            'ROLE_ONBOARDING_PROCESS_LIST',
            'ROLE_ONBOARDING_PROCESS_DETAILS',
            'ROLE_ONBOARDING_PROCESS_COMPLETE',
            'ROLE_ONBOARDING_PROCESS_CANCEL',
        ];
        $onboardingProcessRead = ['ROLE_ONBOARDING_PROCESS_LIST', 'ROLE_ONBOARDING_PROCESS_DETAILS'];

        $onboardingTaskCrud = [
            'ROLE_ONBOARDING_TASK_LIST',
            'ROLE_ONBOARDING_TASK_DETAILS',
            'ROLE_ONBOARDING_TASK_START',
            'ROLE_ONBOARDING_TASK_COMPLETE',
            'ROLE_ONBOARDING_TASK_CANCEL',
        ];
        $onboardingTaskRead = ['ROLE_ONBOARDING_TASK_LIST', 'ROLE_ONBOARDING_TASK_DETAILS'];

        $evaluationCycleCrud = [
            'ROLE_EVALUATION_CYCLE_CREATE',
            'ROLE_EVALUATION_CYCLE_LIST',
            'ROLE_EVALUATION_CYCLE_DETAILS',
            'ROLE_EVALUATION_CYCLE_UPDATE',
            'ROLE_EVALUATION_CYCLE_OPEN',
            'ROLE_EVALUATION_CYCLE_CLOSE',
        ];
        $evaluationCycleRead = ['ROLE_EVALUATION_CYCLE_LIST', 'ROLE_EVALUATION_CYCLE_DETAILS'];

        $performanceReviewCrud = [
            'ROLE_PERFORMANCE_REVIEW_CREATE',
            'ROLE_PERFORMANCE_REVIEW_LIST',
            'ROLE_PERFORMANCE_REVIEW_DETAILS',
            'ROLE_PERFORMANCE_REVIEW_UPDATE',
            'ROLE_PERFORMANCE_REVIEW_SUBMIT',
            'ROLE_PERFORMANCE_REVIEW_VALIDATE',
        ];
        $performanceReviewRead = ['ROLE_PERFORMANCE_REVIEW_LIST', 'ROLE_PERFORMANCE_REVIEW_DETAILS'];

        $objectiveCrud = [
            'ROLE_OBJECTIVE_CREATE',
            'ROLE_OBJECTIVE_LIST',
            'ROLE_OBJECTIVE_DETAILS',
            'ROLE_OBJECTIVE_UPDATE',
            'ROLE_OBJECTIVE_ACTIVATE',
            'ROLE_OBJECTIVE_COMPLETE',
            'ROLE_OBJECTIVE_CANCEL',
        ];
        $objectiveRead = ['ROLE_OBJECTIVE_LIST', 'ROLE_OBJECTIVE_DETAILS'];

        $promotionEligibilityRead = ['ROLE_EMPLOYEE_PROMOTION_ELIGIBILITY'];

        $careerPlanCrud = [
            'ROLE_CAREER_PLAN_CREATE',
            'ROLE_CAREER_PLAN_LIST',
            'ROLE_CAREER_PLAN_DETAILS',
            'ROLE_CAREER_PLAN_UPDATE',
        ];
        $careerPlanRead = ['ROLE_CAREER_PLAN_LIST', 'ROLE_CAREER_PLAN_DETAILS'];

        $mobilityRequestCrud = [
            'ROLE_MOBILITY_REQUEST_CREATE',
            'ROLE_MOBILITY_REQUEST_LIST',
            'ROLE_MOBILITY_REQUEST_DETAILS',
            'ROLE_MOBILITY_REQUEST_SUBMIT',
            'ROLE_MOBILITY_REQUEST_APPROVE',
            'ROLE_MOBILITY_REQUEST_REJECT',
            'ROLE_MOBILITY_REQUEST_CANCEL',
        ];
        $mobilityRequestRead = ['ROLE_MOBILITY_REQUEST_LIST', 'ROLE_MOBILITY_REQUEST_DETAILS'];
        $mobilityRequestManage = [
            'ROLE_MOBILITY_REQUEST_LIST',
            'ROLE_MOBILITY_REQUEST_DETAILS',
            'ROLE_MOBILITY_REQUEST_APPROVE',
            'ROLE_MOBILITY_REQUEST_REJECT',
        ];
        $mobilityRequestSelf = [
            'ROLE_MOBILITY_REQUEST_CREATE',
            'ROLE_MOBILITY_REQUEST_LIST',
            'ROLE_MOBILITY_REQUEST_DETAILS',
            'ROLE_MOBILITY_REQUEST_SUBMIT',
            'ROLE_MOBILITY_REQUEST_CANCEL',
        ];

        $compensationHistoryCrud = [
            'ROLE_COMPENSATION_HISTORY_LIST',
            'ROLE_COMPENSATION_HISTORY_DETAILS',
            'ROLE_COMPENSATION_HISTORY_RECORD',
        ];
        $compensationHistoryRead = ['ROLE_COMPENSATION_HISTORY_LIST', 'ROLE_COMPENSATION_HISTORY_DETAILS'];

        $benefitCrud = [
            'ROLE_BENEFIT_CREATE',
            'ROLE_BENEFIT_LIST',
            'ROLE_BENEFIT_DETAILS',
            'ROLE_BENEFIT_UPDATE',
        ];
        $benefitRead = ['ROLE_BENEFIT_LIST', 'ROLE_BENEFIT_DETAILS'];

        $employeeBenefitCrud = [
            'ROLE_EMPLOYEE_BENEFIT_CREATE',
            'ROLE_EMPLOYEE_BENEFIT_LIST',
            'ROLE_EMPLOYEE_BENEFIT_DETAILS',
            'ROLE_EMPLOYEE_BENEFIT_UPDATE',
        ];
        $employeeBenefitRead = ['ROLE_EMPLOYEE_BENEFIT_LIST', 'ROLE_EMPLOYEE_BENEFIT_DETAILS'];

        $exitProcessCrud = [
            'ROLE_EXIT_PROCESS_CREATE',
            'ROLE_EXIT_PROCESS_LIST',
            'ROLE_EXIT_PROCESS_DETAILS',
            'ROLE_EXIT_PROCESS_START',
            'ROLE_EXIT_PROCESS_COMPLETE',
            'ROLE_EXIT_PROCESS_CANCEL',
        ];
        $exitProcessRead = ['ROLE_EXIT_PROCESS_LIST', 'ROLE_EXIT_PROCESS_DETAILS'];

        $exitTaskCrud = [
            'ROLE_EXIT_TASK_LIST',
            'ROLE_EXIT_TASK_DETAILS',
            'ROLE_EXIT_TASK_START',
            'ROLE_EXIT_TASK_COMPLETE',
            'ROLE_EXIT_TASK_CANCEL',
        ];
        $exitTaskRead = ['ROLE_EXIT_TASK_LIST', 'ROLE_EXIT_TASK_DETAILS'];

        $successionPlanCrud = [
            'ROLE_SUCCESSION_PLAN_CREATE',
            'ROLE_SUCCESSION_PLAN_LIST',
            'ROLE_SUCCESSION_PLAN_DETAILS',
            'ROLE_SUCCESSION_PLAN_UPDATE',
        ];
        $successionPlanRead = ['ROLE_SUCCESSION_PLAN_LIST', 'ROLE_SUCCESSION_PLAN_DETAILS'];
        $hrDashboardView = ['ROLE_HR_DASHBOARD_VIEW'];

        $specs = [
            [
                'label' => 'Super Administrateur',
                'personType' => UserProxyIntertace::PERSON_SUPER_ADMIN,
                'permissions' => $all,
            ],
            [
                'label' => 'Administrateur',
                'personType' => UserProxyIntertace::PERSON_ADMIN,
                'permissions' => array_values(array_filter($all, fn($id) => $id !== 'ROLE_USER_DELETE')),
            ],
            [
                'label' => 'Administrateur RH',
                'personType' => UserProxyIntertace::PERSON_HR_ADMIN,
                'permissions' => array_values(array_unique(array_merge(
                    ['ROLE_USER_LIST', 'ROLE_USER_DETAILS', 'ROLE_USER_CREATE', 'ROLE_USER_EDIT', 'ROLE_USER_LOCK', 'ROLE_USER_CHANGE_PWD', 'ROLE_USER_SET_PROFILE'],
                    $profileRead,
                    $activityRead,
                    $employeeCrud,
                    $workExperienceCrud,
                    $skillCategoryCrud,
                    $skillCrud,
                    $employeeSkillCrud,
                    $jobRoleRequiredSkillCrud,
                    $docCrud,
                    $departmentCrud,
                    $positionCrud,
                    $contractCrud,
                    $leaveRequestCrud,
                    $recruitmentRequestCrud,
                    $jobOfferCrud,
                    $applicationCrud,
                    $trainingRequestCrud,
                    $trainingSessionCrud,
                    $trainingEnrollmentCrud,
                    $trainingCatalogCrud,
                    $jobRoleRequiredTrainingCrud,
                    $onboardingProcessCrud,
                    $onboardingTaskCrud,
                    $evaluationCycleCrud,
                    $performanceReviewCrud,
                    $objectiveCrud,
                    $promotionEligibilityRead,
                    $careerPlanCrud,
                    $mobilityRequestCrud,
                    $compensationHistoryCrud,
                    $benefitCrud,
                    $employeeBenefitCrud,
                    $exitProcessCrud,
                    $exitTaskCrud,
                    $successionPlanCrud,
                    $hrDashboardView,
                ))),
            ],
            [
                'label' => 'Équipe RH (siège)',
                'personType' => UserProxyIntertace::PERSON_HR_STAFF,
                'permissions' => array_values(array_unique(array_merge(
                    $userSelf,
                    $activityRead,
                    $employeeUpdate,
                    $workExperienceRead,
                    $skillCategoryRead,
                    $skillRead,
                    $employeeSkillRead,
                    $jobRoleRequiredSkillRead,
                    ['ROLE_DOC_CREATE', ...$docRead],
                    $departmentRead,
                    $positionRead,
                    $contractRead,
                    $leaveRequestRead,
                    $recruitmentRequestRead,
                    $jobOfferRead,
                    $applicationRead,
                    $trainingRequestRead,
                    $trainingSessionRead,
                    $trainingEnrollmentRead,
                    $trainingCatalogRead,
                    $jobRoleRequiredTrainingRead,
                    $onboardingProcessRead,
                    $onboardingTaskRead,
                    $evaluationCycleRead,
                    $performanceReviewRead,
                    $objectiveRead,
                    $promotionEligibilityRead,
                    $careerPlanRead,
                    $mobilityRequestRead,
                    $compensationHistoryRead,
                    $benefitRead,
                    $employeeBenefitRead,
                    $exitProcessRead,
                    $exitTaskRead,
                    $successionPlanRead,
                    $hrDashboardView,
                ))),
            ],
            [
                'label' => 'Direction',
                'personType' => UserProxyIntertace::PERSON_EXECUTIVE,
                'permissions' => array_values(array_unique(array_merge(
                    $activityRead,
                    $employeeRead,
                    $workExperienceRead,
                    $skillCategoryRead,
                    $skillRead,
                    $employeeSkillRead,
                    $jobRoleRequiredSkillRead,
                    $docRead,
                    $departmentRead,
                    $positionRead,
                    $contractRead,
                    $leaveRequestRead,
                    $promotionEligibilityRead,
                    $careerPlanRead,
                    $mobilityRequestManage,
                    $successionPlanRead,
                    $hrDashboardView,
                ))),
            ],
            [
                'label' => 'Manager',
                'personType' => UserProxyIntertace::PERSON_MANAGER,
                'permissions' => array_values(array_unique(array_merge(
                    $userSelf,
                    $activityRead,
                    $employeeRead,
                    $positionRead,
                    $departmentRead,
                    $contractRead,
                    $docRead,
                    $leaveRequestManage,
                    $promotionEligibilityRead,
                    $mobilityRequestManage,
                ))),
            ],
            [
                'label' => 'RH (province)',
                'personType' => UserProxyIntertace::PERSON_HR_PROVINCE,
                'permissions' => array_values(array_unique(array_merge(
                    $userSelf,
                    $activityRead,
                    $employeeCrud,
                    $workExperienceCrud,
                    $skillCategoryCrud,
                    $skillCrud,
                    $employeeSkillCrud,
                    $jobRoleRequiredSkillCrud,
                    $docCrud,
                    $departmentRead,
                    $positionRead,
                    $contractCrud,
                    $leaveRequestManage,
                    $onboardingProcessCrud,
                    $onboardingTaskCrud,
                    $evaluationCycleCrud,
                    $performanceReviewCrud,
                    $objectiveCrud,
                    $promotionEligibilityRead,
                    $careerPlanCrud,
                    $mobilityRequestCrud,
                    $compensationHistoryCrud,
                    $benefitCrud,
                    $employeeBenefitCrud,
                    $exitProcessCrud,
                    $exitTaskCrud,
                    $successionPlanCrud,
                    $hrDashboardView,
                ))),
            ],
            [
                'label' => 'Employé',
                'personType' => UserProxyIntertace::PERSON_EMPLOYEE,
                'permissions' => array_values(array_unique(array_merge(
                    $userSelf,
                    $employeeUpdate,
                    $workExperienceCrud,
                    $employeeSkillCrud,
                    $skillRead,
                    ['ROLE_DOC_CREATE', ...$docRead],
                    ['ROLE_LEAVE_REQUEST_CREATE', 'ROLE_LEAVE_REQUEST_LIST', 'ROLE_LEAVE_REQUEST_DETAILS', 'ROLE_LEAVE_REQUEST_UPDATE'],
                    $mobilityRequestSelf,
                ))),
            ],
            [
                'label' => 'Consultant',
                'personType' => UserProxyIntertace::PERSON_CONSULTANT,
                'permissions' => array_values(array_unique(array_merge(
                    $userSelf,
                    $docRead,
                    ['ROLE_LEAVE_REQUEST_CREATE', 'ROLE_LEAVE_REQUEST_LIST', 'ROLE_LEAVE_REQUEST_DETAILS', 'ROLE_LEAVE_REQUEST_UPDATE'],
                ))),
            ],
            [
                'label' => 'Stagiaire',
                'personType' => UserProxyIntertace::PERSON_INTERN,
                'permissions' => array_values(array_unique(array_merge(
                    $userSelf,
                    $docRead,
                    ['ROLE_LEAVE_REQUEST_CREATE', 'ROLE_LEAVE_REQUEST_LIST', 'ROLE_LEAVE_REQUEST_DETAILS', 'ROLE_LEAVE_REQUEST_UPDATE'],
                ))),
            ],
            [
                'label' => 'Candidat',
                'personType' => UserProxyIntertace::PERSON_CANDIDATE,
                'permissions' => $positionRead,
            ],
        ];

        foreach ($specs as $spec) {
            $repo = $this->em->getRepository(Profile::class);
            $existing = $repo->findOneBy(['personType' => $spec['personType']]);
            $perms = array_values(array_intersect($all, $spec['permissions']));
            if ($existing) {
                $existing->setLabel($spec['label']);
                $existing->setPermission($perms);
                $existing->setActive(true);
            } else {
                $p = new Profile();
                $p->setLabel($spec['label']);
                $p->setPersonType($spec['personType']);
                $p->setPermission($perms);
                $p->setActive(true);
                $this->em->persist($p);
            }
        }

        $this->em->flush();
        $output->writeln('Profiles seeded.');
        return Command::SUCCESS;
    }
}
