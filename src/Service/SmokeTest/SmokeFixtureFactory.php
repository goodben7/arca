<?php

namespace App\Service\SmokeTest;

use App\Entity\Benefit;
use App\Entity\Contract;
use App\Entity\Employee;
use App\Entity\EmployeeSkill;
use App\Entity\Grade;
use App\Entity\JobRole;
use App\Model\BenefitConstants;
use App\Model\ContractConstants;
use App\Model\EmployeeConstants;
use App\Repository\JobRoleRequiredSkillRepository;
use Doctrine\ORM\EntityManagerInterface;

class SmokeFixtureFactory
{
    public function __construct(
        private EntityManagerInterface $em,
        private JobRoleRequiredSkillRepository $requiredSkills,
    ) {
    }

    public function createEmployeeWithActiveContract(
        string $label,
        JobRole $jobRole,
        Grade $grade,
        string $department,
        string $salary,
        \DateTimeImmutable $hireDate,
    ): array {
        $employee = (new Employee())
            ->setEmployeeNumber(sprintf('SMOKE-%s-%s', $label, date('His')))
            ->setFirstName('Smoke')
            ->setLastName(ucfirst($label))
            ->setGender(EmployeeConstants::GENDER_FEMALE)
            ->setHireDate($hireDate)
            ->setStatus(EmployeeConstants::STATUS_ACTIVE)
            ->setDepartment($department)
            ->setJobRole($jobRole)
            ->setGrade($grade)
            ->setCreatedBy('SYSTEM');

        $this->em->persist($employee);
        $this->em->flush();

        $contract = (new Contract())
            ->setEmployee((string) $employee->getId())
            ->setType(ContractConstants::TYPE_CDI)
            ->setStartDate($hireDate)
            ->setSalary($salary)
            ->setStatus(ContractConstants::STATUS_ACTIVE);

        $this->em->persist($contract);
        $this->em->flush();

        return ['employee' => $employee, 'contract' => $contract];
    }

    public function createBenefit(string $label): Benefit
    {
        $benefit = (new Benefit())
            ->setCode(sprintf('SMOKE-BF-%s-%s', $label, date('His')))
            ->setName(sprintf('Smoke benefit %s', $label))
            ->setType(BenefitConstants::TYPE_HEALTH)
            ->setDescription('Created by ar:smoke:test');

        $this->em->persist($benefit);
        $this->em->flush();

        return $benefit;
    }

    public function grantRequiredSkillsForJobRole(Employee $employee, JobRole $targetJobRole): void
    {
        foreach ($this->requiredSkills->findByJobRole($targetJobRole) as $requirement) {
            $skill = $requirement->getSkill();
            if (null === $skill) {
                continue;
            }

            $employeeSkill = (new EmployeeSkill())
                ->setEmployee((string) $employee->getId())
                ->setSkill($skill)
                ->setLevel($requirement->getMinimumLevel())
                ->setValidatedAt(new \DateTimeImmutable());

            $this->em->persist($employeeSkill);
        }

        $this->em->flush();
    }
}
