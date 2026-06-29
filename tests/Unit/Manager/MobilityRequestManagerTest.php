<?php

namespace App\Tests\Unit\Manager;

use App\Entity\Employee;
use App\Entity\MobilityRequest;
use App\Event\Domain\MobilityImplementedEvent;
use App\Exception\InvalidActionInputException;
use App\Manager\GradeManager;
use App\Manager\JobRoleManager;
use App\Manager\MobilityRequestManager;
use App\Message\Query\QueryBusInterface;
use App\Model\EmployeeConstants;
use App\Model\MobilityRequestConstants;
use App\Model\NewMobilityRequestModel;
use App\Model\SubmitMobilityRequestModel;
use App\Policy\PolicyEvaluator;
use App\Policy\PolicyResult;
use App\Service\ActivityEventDispatcher;
use App\Workflow\MobilityApprovalWorkflow;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class MobilityRequestManagerTest extends ManagerTestCase
{
    private EntityManagerInterface&MockObject $em;
    private ActivityEventDispatcher&MockObject $eventDispatcher;
    private Security&MockObject $security;
    private QueryBusInterface&MockObject $queries;
    private EventDispatcherInterface&MockObject $domainEventDispatcher;
    private JobRoleManager&MockObject $jobRoles;
    private GradeManager&MockObject $grades;
    private PolicyEvaluator&MockObject $policyEvaluator;
    private MobilityRequestManager $manager;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->eventDispatcher = $this->createMock(ActivityEventDispatcher::class);
        $this->security = $this->createMock(Security::class);
        $this->queries = $this->createMock(QueryBusInterface::class);
        $this->domainEventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->jobRoles = $this->createMock(JobRoleManager::class);
        $this->grades = $this->createMock(GradeManager::class);
        $this->policyEvaluator = $this->createMock(PolicyEvaluator::class);

        $this->security->method('getUser')->willReturn(null);

        $this->manager = new MobilityRequestManager(
            $this->em,
            $this->eventDispatcher,
            $this->security,
            $this->queries,
            $this->domainEventDispatcher,
            new MobilityApprovalWorkflow(),
            $this->jobRoles,
            $this->grades,
            $this->policyEvaluator,
        );
    }

    public function testSubmitPromotionChecksPolicy(): void
    {
        $family = $this->createJobFamily('JFTEST001', 'FIN');
        $grade = $this->createGrade('GRTEST001', 'G3', 3);
        $targetRole = $this->createJobRole('JRTEST002', 'SR_ACC', $family, $grade);
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);
        $employee->setJobRole($this->createJobRole('JRTEST001', 'ACC', $family, $this->createGrade('GRTEST000', 'G2', 2)));

        $request = (new MobilityRequest())
            ->setEmployee('EMTEST001')
            ->setType(MobilityRequestConstants::TYPE_PROMOTION)
            ->setStatus(MobilityRequestConstants::STATUS_DRAFT)
            ->setTargetJobRole($targetRole);
        $this->setEntityId($request, 'MBTEST001');

        $this->em->method('find')->willReturnCallback(function (string $class, $id) use ($request, $employee) {
            if (MobilityRequest::class === $class) {
                return $request;
            }
            if (Employee::class === $class) {
                return $employee;
            }

            return null;
        });

        $this->policyEvaluator
            ->expects($this->once())
            ->method('evaluate')
            ->willReturn(PolicyResult::notEligible(['no career path defined for this transition']));

        $this->expectException(InvalidActionInputException::class);
        $this->expectExceptionMessage('no career path defined for this transition');

        $this->manager->submitFrom(new SubmitMobilityRequestModel('MBTEST001'));
    }

    public function testApproveExecutiveDispatchesImplementedEvent(): void
    {
        $request = (new MobilityRequest())
            ->setEmployee('EMTEST001')
            ->setType(MobilityRequestConstants::TYPE_TRANSFER)
            ->setStatus(MobilityRequestConstants::STATUS_EXECUTIVE_APPROVAL)
            ->setTargetDepartment('Finance');
        $this->setEntityId($request, 'MBTEST001');

        $this->em->method('find')->willReturn($request);

        $this->domainEventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(self::callback(fn (object $event) => $event instanceof MobilityImplementedEvent));

        $result = $this->manager->approve('MBTEST001');

        self::assertSame(MobilityRequestConstants::STATUS_IMPLEMENTED, $result->getStatus());
    }

    public function testCreatePromotionRequiresTargetJobRole(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);

        $this->em->method('find')->willReturn($employee);

        $this->expectException(InvalidActionInputException::class);
        $this->expectExceptionMessage('targetJobRoleId is required');

        $this->manager->createFrom(new NewMobilityRequestModel(
            'EMTEST001',
            MobilityRequestConstants::TYPE_PROMOTION,
        ));
    }
}
