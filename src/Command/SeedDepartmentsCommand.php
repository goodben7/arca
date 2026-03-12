<?php

namespace App\Command;

use App\Entity\Department;
use App\Entity\Employee;
use App\Entity\Profile;
use App\Entity\Position;
use App\Entity\User;
use App\Manager\EmployeeManager;
use App\Manager\PermissionManager;
use App\Model\EmployeeConstants;
use App\Model\NewEmployeeModel;
use App\Model\PositionLevel;
use App\Model\PositionStatusConstants;
use App\Model\UserProxyIntertace;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'ar:seed:departments',
    description: 'Seed departments',
)]
class SeedDepartmentsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private EmployeeManager $employeeManager,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $departmentRepo = $this->em->getRepository(Department::class);
        $employeeRepo = $this->em->getRepository(Employee::class);
        $positionRepo = $this->em->getRepository(Position::class);
        $profileRepo = $this->em->getRepository(Profile::class);
        $userRepo = $this->em->getRepository(User::class);

        $total = $departmentRepo->count([]);
        if ($total > 0) {
            $output->writeln('Seed aborted: la base de données n’est pas vide.');
            return Command::FAILURE;
        }

        $managerProfile = $this->createManagerProfile();

        $departments = $this->createDepartments();
        $this->em->flush();

        $managerSpecs = [
            'HR' => [
                'firstName' => 'Jean',
                'lastName' => 'Diallo',
                'gender' => EmployeeConstants::GENDER_MALE,
                'hireDate' => new \DateTimeImmutable('2022-06-01'),
                'birthDate' => new \DateTimeImmutable('1990-04-12'),
                'nationality' => 'CI',
                'maritalStatus' => EmployeeConstants::MARITAL_MARRIED,
                'phone' => '+2250102030405',
                'positionTitle' => 'Responsable RH',
                'positionDescription' => 'Pilotage des opérations RH et management du département',
            ],
            'IT' => [
                'firstName' => 'Awa',
                'lastName' => 'Koné',
                'gender' => EmployeeConstants::GENDER_FEMALE,
                'hireDate' => new \DateTimeImmutable('2021-11-15'),
                'birthDate' => new \DateTimeImmutable('1991-08-03'),
                'nationality' => 'CI',
                'maritalStatus' => EmployeeConstants::MARITAL_SINGLE,
                'phone' => '+2250102030406',
                'positionTitle' => 'Responsable Informatique',
                'positionDescription' => 'Supervision SI, applications, support et sécurité',
            ],
            'FIN' => [
                'firstName' => 'Mariam',
                'lastName' => 'Traoré',
                'gender' => EmployeeConstants::GENDER_FEMALE,
                'hireDate' => new \DateTimeImmutable('2020-01-20'),
                'birthDate' => new \DateTimeImmutable('1988-02-19'),
                'nationality' => 'CI',
                'maritalStatus' => EmployeeConstants::MARITAL_MARRIED,
                'phone' => '+2250102030407',
                'positionTitle' => 'Responsable Finance',
                'positionDescription' => 'Comptabilité, trésorerie, budget et contrôle interne',
            ],
            'OPS' => [
                'firstName' => 'Ibrahim',
                'lastName' => 'Coulibaly',
                'gender' => EmployeeConstants::GENDER_MALE,
                'hireDate' => new \DateTimeImmutable('2019-09-02'),
                'birthDate' => new \DateTimeImmutable('1987-12-09'),
                'nationality' => 'CI',
                'maritalStatus' => EmployeeConstants::MARITAL_MARRIED,
                'phone' => '+2250102030408',
                'positionTitle' => 'Responsable Opérations',
                'positionDescription' => 'Exploitation, logistique et amélioration continue',
            ],
            'SALES' => [
                'firstName' => 'Fatou',
                'lastName' => 'Bamba',
                'gender' => EmployeeConstants::GENDER_FEMALE,
                'hireDate' => new \DateTimeImmutable('2023-03-13'),
                'birthDate' => new \DateTimeImmutable('1993-05-27'),
                'nationality' => 'CI',
                'maritalStatus' => EmployeeConstants::MARITAL_SINGLE,
                'phone' => '+2250102030409',
                'positionTitle' => 'Responsable Commercial',
                'positionDescription' => 'Ventes, partenariats et relation client',
            ],
        ];

        foreach ($departments as $departmentCode => $department) {
            $spec = $managerSpecs[$departmentCode] ?? null;
            if (!$spec || !$department->getId() || !$department->getCode()) {
                $output->writeln(sprintf('Departments seeded. ERREUR: données manquantes pour %s.', $departmentCode));
                return Command::FAILURE;
            }

            $position = $this->createManagerPosition(
                $department->getId(),
                $spec['positionTitle'],
                $spec['positionDescription'],
            );
            $this->em->flush();

            $email = $this->generateUniqueManagerEmail($userRepo, strtolower($departmentCode));
            $employee = $this->employeeManager->createFrom(new NewEmployeeModel(
                $spec['firstName'],
                $spec['lastName'],
                $spec['gender'],
                $spec['hireDate'],
                $email,
                $spec['phone'],
                $spec['birthDate'],
                $spec['nationality'],
                $spec['maritalStatus'],
                null,
                $department->getId(),
                $position->getId(),
                null,
                null,
                $managerProfile,
            ));

            $department->setManagerId($employee->getId());
            $output->writeln(sprintf('%s manager created: %s (%s)', $departmentCode, $employee->getId(), $email));
        }

        $this->em->flush();
        $output->writeln('Departments seeded (with managerId).');

        return Command::SUCCESS;
    }

    private function createManagerProfile(): Profile
    {
        $pm = PermissionManager::getInstance();
        $all = array_map(fn($p) => $p->getPermissionId(), $pm->getPermissions());
        $default = [
            'ROLE_USER_CHANGE_PWD',
            'ROLE_USER_DETAILS',
            'ROLE_ACTIVITY_LIST',
            'ROLE_ACTIVITY_VIEW',
            'ROLE_EMPLOYEE_LIST',
            'ROLE_EMPLOYEE_DETAILS',
            'ROLE_DEPARTMENT_LIST',
            'ROLE_DEPARTMENT_DETAILS',
            'ROLE_POSITION_LIST',
            'ROLE_POSITION_DETAILS',
            'ROLE_CONTRACT_LIST',
            'ROLE_CONTRACT_DETAILS',
            'ROLE_DOC_LIST',
            'ROLE_DOC_DETAILS',
            'ROLE_LEAVE_REQUEST_LIST',
            'ROLE_LEAVE_REQUEST_DETAILS',
            'ROLE_LEAVE_REQUEST_APPROVE',
            'ROLE_LEAVE_REQUEST_REJECT',
        ];

        $profile = new Profile();
        $profile
            ->setLabel('Manager')
            ->setPersonType(UserProxyIntertace::PERSON_MANAGER)
            ->setPermission(array_values(array_intersect($all, $default)))
            ->setActive(true)
        ;
        $this->em->persist($profile);

        return $profile;
    }

    private function generateUniqueManagerEmail(mixed $userRepo, string $dept): string
    {
        $base = sprintf('manager.%s@arca.local', $dept);
        $email = $base;
        $i = 1;
        while ($userRepo->findOneBy(['email' => $email])) {
            $email = sprintf('manager.%s%d@arca.local', $dept, $i);
            $i++;
        }

        return $email;
    }

    /**
     * @return array<string, Department>
     */
    private function createDepartments(): array
    {
        $specs = [
            ['code' => 'HR', 'name' => 'Ressources Humaines', 'description' => 'Administration du personnel, paie et politique RH'],
            ['code' => 'IT', 'name' => 'Informatique', 'description' => 'Infrastructure, applications, support et sécurité'],
            ['code' => 'FIN', 'name' => 'Finance', 'description' => 'Comptabilité, trésorerie, budget et contrôle interne'],
            ['code' => 'OPS', 'name' => 'Opérations', 'description' => 'Exploitation, logistique et amélioration continue'],
            ['code' => 'SALES', 'name' => 'Commercial', 'description' => 'Ventes, partenariats et relation client'],
        ];

        $departments = [];
        foreach ($specs as $spec) {
            $department = new Department();
            $department
                ->setCode($spec['code'])
                ->setName($spec['name'])
                ->setDescription($spec['description'])
            ;
            $this->em->persist($department);
            $departments[$spec['code']] = $department;
        }

        return $departments;
    }

    private function createManagerPosition(string $departmentId, string $title, string $description): Position
    {
        $position = new Position();
        $position
            ->setTitle($title)
            ->setDepartment($departmentId)
            ->setLevel(PositionLevel::MANAGER)
            ->setDescription($description)
            ->setHeadcount(1)
            ->setOpenPositions(0)
            ->setStatus(PositionStatusConstants::STATUS_OPEN)
        ;
        $this->em->persist($position);

        return $position;
    }
}
