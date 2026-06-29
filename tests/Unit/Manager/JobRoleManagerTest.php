<?php

namespace App\Tests\Unit\Manager;

use App\Entity\JobFamily;
use App\Entity\JobRole;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Manager\GradeManager;
use App\Manager\JobFamilyManager;
use App\Manager\JobRoleManager;
use App\Repository\JobRoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

class JobRoleManagerTest extends ManagerTestCase
{
    private EntityManagerInterface&MockObject $em;
    private JobRoleRepository&MockObject $repository;
    private JobFamilyManager&MockObject $jobFamilies;
    private GradeManager&MockObject $grades;
    private JobRoleManager $manager;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(JobRoleRepository::class);
        $this->jobFamilies = $this->createMock(JobFamilyManager::class);
        $this->grades = $this->createMock(GradeManager::class);

        $this->manager = new JobRoleManager(
            $this->em,
            $this->repository,
            $this->jobFamilies,
            $this->grades,
        );
    }

    public function testFindReturnsJobRole(): void
    {
        $family = $this->createJobFamily('JFTEST001', 'FIN');
        $grade = $this->createGrade('GRTEST001', 'G2', 2);
        $jobRole = $this->createJobRole('JRTEST001', 'ACC', $family, $grade);

        $this->em->method('find')->with(JobRole::class, 'JRTEST001')->willReturn($jobRole);

        self::assertSame($jobRole, $this->manager->find('JRTEST001'));
    }

    public function testFindThrowsWhenJobRoleMissing(): void
    {
        $this->em->method('find')->willReturn(null);

        $this->expectException(UnavailableDataException::class);
        $this->manager->find('JRMISSING');
    }

    public function testAssertCodeAvailableThrowsWhenCodeAlreadyExists(): void
    {
        $family = $this->createJobFamily('JFTEST001', 'FIN');
        $grade = $this->createGrade('GRTEST001', 'G2', 2);
        $existing = $this->createJobRole('JRTEST001', 'ACC', $family, $grade);

        $this->repository->method('findOneByCode')->with('ACC')->willReturn($existing);

        $this->expectException(InvalidActionInputException::class);
        $this->manager->assertCodeAvailable('ACC');
    }

    public function testResolveJobFamilyReturnsEntityWhenAlreadyResolved(): void
    {
        $family = $this->createJobFamily('JFTEST001', 'FIN');

        self::assertSame($family, $this->manager->resolveJobFamily($family));
    }

    public function testResolveJobFamilyDelegatesToManagerWhenIdProvided(): void
    {
        $family = $this->createJobFamily('JFTEST001', 'FIN');
        $this->jobFamilies->expects($this->once())->method('find')->with('JFTEST001')->willReturn($family);

        self::assertSame($family, $this->manager->resolveJobFamily('JFTEST001'));
    }
}
