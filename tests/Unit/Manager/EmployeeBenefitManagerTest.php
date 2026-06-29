<?php

namespace App\Tests\Unit\Manager;

use App\Entity\Benefit;
use App\Entity\Employee;
use App\Event\ActivityEvent;
use App\Exception\InvalidActionInputException;
use App\Manager\EmployeeBenefitManager;
use App\Model\BenefitConstants;
use App\Model\EmployeeBenefitConstants;
use App\Model\EmployeeConstants;
use App\Model\NewEmployeeBenefitModel;
use App\Repository\EmployeeBenefitRepository;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

class EmployeeBenefitManagerTest extends ManagerTestCase
{
    private EntityManagerInterface&MockObject $em;
    private ActivityEventDispatcher&MockObject $eventDispatcher;
    private EmployeeBenefitRepository&MockObject $employeeBenefits;
    private EmployeeBenefitManager $manager;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->eventDispatcher = $this->createMock(ActivityEventDispatcher::class);
        $this->employeeBenefits = $this->createMock(EmployeeBenefitRepository::class);

        $this->manager = new EmployeeBenefitManager(
            $this->em,
            $this->eventDispatcher,
            $this->employeeBenefits,
        );
    }

    public function testCreateFromAssignsActiveBenefit(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);
        $benefit = (new Benefit())
            ->setCode('HEALTH-01')
            ->setName('Mutuelle')
            ->setType(BenefitConstants::TYPE_HEALTH);
        $this->setEntityId($benefit, 'BFTEST001');

        $this->em->method('find')->willReturnCallback(function (string $class, $id) use ($employee, $benefit) {
            if (Employee::class === $class) {
                return $employee;
            }
            if (Benefit::class === $class) {
                return $benefit;
            }

            return null;
        });

        $this->employeeBenefits->method('findOneBy')->willReturn(null);
        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');
        $this->eventDispatcher->expects($this->once())->method('dispatch');

        $enrollment = $this->manager->createFrom(new NewEmployeeBenefitModel(
            'EMTEST001',
            'BFTEST001',
            new \DateTimeImmutable('2026-01-01'),
        ));

        self::assertSame(EmployeeBenefitConstants::STATUS_ACTIVE, $enrollment->getStatus());
        self::assertSame('EMTEST001', $enrollment->getEmployee());
    }

    public function testCreateFromRejectsDuplicateActiveEnrollment(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);
        $benefit = (new Benefit())->setCode('HEALTH-01')->setName('Mutuelle')->setType(BenefitConstants::TYPE_HEALTH);
        $this->setEntityId($benefit, 'BFTEST001');

        $this->em->method('find')->willReturnCallback(function (string $class) use ($employee, $benefit) {
            return match ($class) {
                Employee::class => $employee,
                Benefit::class => $benefit,
                default => null,
            };
        });

        $this->employeeBenefits->method('findOneBy')->willReturn(new \App\Entity\EmployeeBenefit());

        $this->expectException(InvalidActionInputException::class);

        $this->manager->createFrom(new NewEmployeeBenefitModel(
            'EMTEST001',
            'BFTEST001',
            new \DateTimeImmutable('2026-01-01'),
        ));
    }
}
