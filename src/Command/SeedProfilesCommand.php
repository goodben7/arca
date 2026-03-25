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

        $skillCrud = [
            'ROLE_SKILL_CREATE',
            'ROLE_SKILL_LIST',
            'ROLE_SKILL_DETAILS',
            'ROLE_SKILL_UPDATE',
        ];
        $skillRead = ['ROLE_SKILL_LIST', 'ROLE_SKILL_DETAILS'];

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
                    $skillCrud,
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
                    $skillRead,
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
                ))),
            ],
            [
                'label' => 'Direction',
                'personType' => UserProxyIntertace::PERSON_EXECUTIVE,
                'permissions' => array_values(array_unique(array_merge(
                    $activityRead,
                    $employeeRead,
                    $workExperienceRead,
                    $skillRead,
                    $docRead,
                    $departmentRead,
                    $positionRead,
                    $contractRead,
                    $leaveRequestRead,
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
                    $skillCrud,
                    $docCrud,
                    $departmentRead,
                    $positionRead,
                    $contractCrud,
                    $leaveRequestManage,
                ))),
            ],
            [
                'label' => 'Employé',
                'personType' => UserProxyIntertace::PERSON_EMPLOYEE,
                'permissions' => array_values(array_unique(array_merge(
                    $userSelf,
                    $employeeUpdate,
                    $workExperienceCrud,
                    $skillCrud,
                    ['ROLE_DOC_CREATE', ...$docRead],
                    ['ROLE_LEAVE_REQUEST_CREATE', 'ROLE_LEAVE_REQUEST_LIST', 'ROLE_LEAVE_REQUEST_DETAILS', 'ROLE_LEAVE_REQUEST_UPDATE'],
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
