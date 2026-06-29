<?php

namespace App\Tests\Unit\Manager;

use App\Entity\Department;
use App\Entity\JobOffer;
use App\Entity\RecruitmentRequest;
use App\Manager\JobOfferManager;
use App\Manager\JobRoleManager;
use App\Message\Query\QueryBusInterface;
use App\Model\JobOfferConstants;
use App\Model\NewJobOfferModel;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;

class JobOfferManagerTest extends ManagerTestCase
{
    private EntityManagerInterface&MockObject $em;
    private ActivityEventDispatcher&MockObject $eventDispatcher;
    private Security&MockObject $security;
    private QueryBusInterface&MockObject $queries;
    private JobRoleManager&MockObject $jobRoles;
    private JobOfferManager $manager;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->eventDispatcher = $this->createMock(ActivityEventDispatcher::class);
        $this->security = $this->createMock(Security::class);
        $this->queries = $this->createMock(QueryBusInterface::class);
        $this->jobRoles = $this->createMock(JobRoleManager::class);

        $this->security->method('getUser')->willReturn(null);

        $this->manager = new JobOfferManager(
            $this->em,
            $this->eventDispatcher,
            $this->security,
            $this->queries,
            $this->jobRoles,
        );
    }

    public function testCreateFromLinksJobRole(): void
    {
        $department = (new Department())->setCode('FIN')->setName('Finance');
        $this->setEntityId($department, 'DPTEST001');

        $recruitmentRequest = (new RecruitmentRequest())
            ->setDepartment('DPTEST001')
            ->setPosition('POTEST001')
            ->setNumberOfPositions(1)
            ->setJustification('Besoin comptable')
            ->setStatus('PENDING');
        $this->setEntityId($recruitmentRequest, 'RRTEST001');

        $family = $this->createJobFamily('JFTEST001', 'FIN');
        $grade = $this->createGrade('GRTEST001', 'G2', 2);
        $jobRole = $this->createJobRole('JRTEST001', 'ACC', $family, $grade);

        $this->em->method('find')->willReturnMap([
            [Department::class, 'DPTEST001', $department],
            [RecruitmentRequest::class, 'RRTEST001', $recruitmentRequest],
        ]);

        $this->jobRoles
            ->expects($this->once())
            ->method('find')
            ->with('JRTEST001')
            ->willReturn($jobRole);

        $persistedOffer = null;
        $this->em->expects($this->once())->method('persist')->willReturnCallback(function (JobOffer $offer) use (&$persistedOffer): void {
            $persistedOffer = $offer;
        });
        $this->em->expects($this->once())->method('flush');
        $this->eventDispatcher->expects($this->once())->method('dispatch');

        $result = $this->manager->createFrom(new NewJobOfferModel(
            'Comptable',
            'Recrutement comptable confirmé',
            'DPTEST001',
            'RRTEST001',
            'JRTEST001',
        ));

        self::assertSame($persistedOffer, $result);
        self::assertSame($jobRole, $result->getJobRole());
        self::assertSame('DPTEST001', $result->getDepartment());
        self::assertSame('RRTEST001', $result->getRecruitmentRequest());
        self::assertSame(JobOfferConstants::STATUS_DRAFT, $result->getStatus());
    }
}
