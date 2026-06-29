<?php

namespace App\Tests\Unit\Manager;

use App\Entity\CareerPath;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Manager\CareerPathManager;
use App\Manager\JobRoleManager;
use App\Repository\CareerPathRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

class CareerPathManagerTest extends ManagerTestCase
{
    private EntityManagerInterface&MockObject $em;
    private CareerPathRepository&MockObject $repository;
    private JobRoleManager&MockObject $jobRoles;
    private CareerPathManager $manager;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(CareerPathRepository::class);
        $this->jobRoles = $this->createMock(JobRoleManager::class);

        $this->manager = new CareerPathManager(
            $this->em,
            $this->repository,
            $this->jobRoles,
        );
    }

    public function testAssertValidTransitionRejectsSameJobRole(): void
    {
        $family = $this->createJobFamily('JFTEST001', 'FIN');
        $grade = $this->createGrade('GRTEST001', 'G2', 2);
        $role = $this->createJobRole('JRTEST001', 'ACC', $family, $grade);

        $this->expectException(InvalidActionInputException::class);
        $this->manager->assertValidTransition($role, $role);
    }

    public function testAssertValidTransitionRejectsDuplicateTransition(): void
    {
        $family = $this->createJobFamily('JFTEST001', 'FIN');
        $from = $this->createJobRole('JRTEST001', 'ACC-JUNIOR', $family, $this->createGrade('GRTEST001', 'G1', 1));
        $to = $this->createJobRole('JRTEST002', 'ACC', $family, $this->createGrade('GRTEST002', 'G2', 2));
        $existing = (new CareerPath())->setFromJobRole($from)->setToJobRole($to);
        $this->setEntityId($existing, 'CPTEST001');

        $this->repository->method('findOneByTransition')->with($from, $to)->willReturn($existing);

        $this->expectException(InvalidActionInputException::class);
        $this->manager->assertValidTransition($from, $to);
    }

    public function testAssertValidTransitionAcceptsNewTransition(): void
    {
        $family = $this->createJobFamily('JFTEST001', 'FIN');
        $from = $this->createJobRole('JRTEST001', 'ACC-JUNIOR', $family, $this->createGrade('GRTEST001', 'G1', 1));
        $to = $this->createJobRole('JRTEST002', 'ACC', $family, $this->createGrade('GRTEST002', 'G2', 2));

        $this->repository->method('findOneByTransition')->with($from, $to)->willReturn(null);

        $this->manager->assertValidTransition($from, $to);

        self::assertTrue(true);
    }

    public function testFindThrowsWhenCareerPathMissing(): void
    {
        $this->em->method('find')->with(CareerPath::class, 'CPMISSING')->willReturn(null);

        $this->expectException(UnavailableDataException::class);
        $this->manager->find('CPMISSING');
    }
}
