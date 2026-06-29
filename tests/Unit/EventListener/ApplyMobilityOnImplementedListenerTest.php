<?php

namespace App\Tests\Unit\EventListener;

use App\Entity\Employee;
use App\Entity\MobilityRequest;
use App\Event\Domain\MobilityImplementedEvent;
use App\EventListener\ApplyMobilityOnImplementedListener;
use App\Model\EmployeeConstants;
use App\Model\MobilityRequestConstants;
use App\Tests\Unit\Manager\ManagerTestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

class ApplyMobilityOnImplementedListenerTest extends ManagerTestCase
{
    private EntityManagerInterface&MockObject $em;
    private ApplyMobilityOnImplementedListener $listener;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->listener = new ApplyMobilityOnImplementedListener($this->em);
    }

    public function testUpdatesEmployeeJobRoleGradeAndDepartment(): void
    {
        $family = $this->createJobFamily('JFTEST001', 'FIN');
        $targetGrade = $this->createGrade('GRTEST002', 'G4', 4);
        $targetRole = $this->createJobRole('JRTEST002', 'SR_ACC', $family, $targetGrade);

        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);
        $employee->setJobRole($this->createJobRole('JRTEST001', 'ACC', $family, $this->createGrade('GRTEST001', 'G2', 2)));
        $employee->setDepartment('Comptabilité');

        $request = (new MobilityRequest())
            ->setEmployee('EMTEST001')
            ->setType(MobilityRequestConstants::TYPE_PROMOTION)
            ->setTargetJobRole($targetRole)
            ->setTargetGrade($targetGrade)
            ->setTargetDepartment('Finance');
        $this->setEntityId($request, 'MBTEST001');

        $this->em->method('find')->with(Employee::class, 'EMTEST001')->willReturn($employee);
        $this->em->expects($this->once())->method('flush');

        $this->listener->onMobilityImplemented(new MobilityImplementedEvent($request, 'USTEST001'));

        self::assertSame($targetRole, $employee->getJobRole());
        self::assertSame($targetGrade, $employee->getGrade());
        self::assertSame('Finance', $employee->getDepartment());
    }

    public function testUsesJobRoleGradeWhenTargetGradeNotSet(): void
    {
        $family = $this->createJobFamily('JFTEST001', 'FIN');
        $roleGrade = $this->createGrade('GRTEST003', 'G3', 3);
        $targetRole = $this->createJobRole('JRTEST002', 'SR_ACC', $family, $roleGrade);

        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);

        $request = (new MobilityRequest())
            ->setEmployee('EMTEST001')
            ->setType(MobilityRequestConstants::TYPE_PROMOTION)
            ->setTargetJobRole($targetRole);
        $this->setEntityId($request, 'MBTEST001');

        $this->em->method('find')->with(Employee::class, 'EMTEST001')->willReturn($employee);
        $this->em->expects($this->once())->method('flush');

        $this->listener->onMobilityImplemented(new MobilityImplementedEvent($request, 'USTEST001'));

        self::assertSame($targetRole, $employee->getJobRole());
        self::assertSame($roleGrade, $employee->getGrade());
    }

    public function testTransferUpdatesDepartmentOnlyWhenNoJobRole(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);
        $employee->setDepartment('Comptabilité');

        $request = (new MobilityRequest())
            ->setEmployee('EMTEST001')
            ->setType(MobilityRequestConstants::TYPE_TRANSFER)
            ->setTargetDepartment('Audit');
        $this->setEntityId($request, 'MBTEST001');

        $this->em->method('find')->with(Employee::class, 'EMTEST001')->willReturn($employee);
        $this->em->expects($this->once())->method('flush');

        $this->listener->onMobilityImplemented(new MobilityImplementedEvent($request, 'USTEST001'));

        self::assertSame('Audit', $employee->getDepartment());
    }

    public function testDoesNothingWhenEmployeeNotFound(): void
    {
        $request = (new MobilityRequest())
            ->setEmployee('EMMISSING')
            ->setType(MobilityRequestConstants::TYPE_TRANSFER)
            ->setTargetDepartment('Audit');
        $this->setEntityId($request, 'MBTEST001');

        $this->em->method('find')->with(Employee::class, 'EMMISSING')->willReturn(null);
        $this->em->expects($this->never())->method('flush');

        $this->listener->onMobilityImplemented(new MobilityImplementedEvent($request, 'USTEST001'));
    }
}
