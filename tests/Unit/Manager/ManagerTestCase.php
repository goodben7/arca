<?php

namespace App\Tests\Unit\Manager;

use App\Entity\Contract;
use App\Entity\Employee;
use App\Entity\Grade;
use App\Entity\JobFamily;
use App\Entity\JobRole;
use App\Entity\LeaveRequest;
use App\Model\ContractConstants;
use App\Model\EmployeeConstants;
use App\Model\LeaveRequestConstants;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

abstract class ManagerTestCase extends TestCase
{
    protected function setEntityId(object $entity, string $id): void
    {
        $property = new ReflectionProperty($entity, 'id');
        $property->setValue($entity, $id);
    }

    protected function createEmployee(string $id, string $status): Employee
    {
        $employee = (new Employee())
            ->setFirstName('Jane')
            ->setLastName('Doe')
            ->setGender(EmployeeConstants::GENDER_FEMALE)
            ->setHireDate(new \DateTimeImmutable('2024-01-01'))
            ->setEmployeeNumber('EMP-TEST-001')
            ->setStatus($status)
            ->setCreatedAt(new \DateTimeImmutable('2024-01-01'));

        $this->setEntityId($employee, $id);

        return $employee;
    }

    protected function createContract(string $id, string $status): Contract
    {
        $contract = (new Contract())
            ->setEmployee('EMTEST001')
            ->setType(ContractConstants::TYPE_CDI)
            ->setStartDate(new \DateTimeImmutable('2024-01-01'))
            ->setSalary('45000.00')
            ->setStatus($status)
            ->setCreatedAt(new \DateTimeImmutable('2024-01-01'));

        $this->setEntityId($contract, $id);

        return $contract;
    }

    protected function createJobFamily(string $id, string $code): JobFamily
    {
        $family = (new JobFamily())
            ->setCode($code)
            ->setName('Finance')
            ->setCreatedAt(new \DateTimeImmutable('2024-01-01'));

        $this->setEntityId($family, $id);

        return $family;
    }

    protected function createGrade(string $id, string $code, int $rank = 1): Grade
    {
        $grade = (new Grade())
            ->setCode($code)
            ->setName('Grade '.$code)
            ->setRank($rank)
            ->setCreatedAt(new \DateTimeImmutable('2024-01-01'));

        $this->setEntityId($grade, $id);

        return $grade;
    }

    protected function createJobRole(string $id, string $code, JobFamily $family, Grade $grade): JobRole
    {
        $role = (new JobRole())
            ->setCode($code)
            ->setTitle('Comptable')
            ->setJobFamily($family)
            ->setGrade($grade)
            ->setCreatedAt(new \DateTimeImmutable('2024-01-01'));

        $this->setEntityId($role, $id);

        return $role;
    }

    protected function createLeaveRequest(
        string $id,
        string $status = LeaveRequestConstants::STATUS_PENDING,
        string $type = LeaveRequestConstants::TYPE_ANNUAL,
    ): LeaveRequest {
        $leaveRequest = (new LeaveRequest())
            ->setEmployee('EMTEST001')
            ->setType($type)
            ->setStartDate(new \DateTimeImmutable('2025-07-01'))
            ->setEndDate(new \DateTimeImmutable('2025-07-05'))
            ->setNumberOfDays(5)
            ->setStatus($status)
            ->setCreatedAt(new \DateTimeImmutable('2025-06-01'));

        $this->setEntityId($leaveRequest, $id);

        return $leaveRequest;
    }
}
