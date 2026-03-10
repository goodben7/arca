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
        ];
        $positionRead = ['ROLE_POSITION_LIST', 'ROLE_POSITION_DETAILS'];

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
