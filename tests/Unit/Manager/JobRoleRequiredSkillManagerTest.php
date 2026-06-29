<?php

namespace App\Tests\Unit\Manager;

use App\Entity\JobRoleRequiredSkill;
use App\Entity\Skill;
use App\Entity\SkillCategory;
use App\Exception\InvalidActionInputException;
use App\Manager\JobRoleManager;
use App\Manager\JobRoleRequiredSkillManager;
use App\Manager\SkillManager;
use App\Repository\JobRoleRequiredSkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

class JobRoleRequiredSkillManagerTest extends ManagerTestCase
{
    private EntityManagerInterface&MockObject $em;
    private JobRoleRequiredSkillRepository&MockObject $repository;
    private JobRoleManager&MockObject $jobRoles;
    private SkillManager&MockObject $skills;
    private JobRoleRequiredSkillManager $manager;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(JobRoleRequiredSkillRepository::class);
        $this->jobRoles = $this->createMock(JobRoleManager::class);
        $this->skills = $this->createMock(SkillManager::class);

        $this->manager = new JobRoleRequiredSkillManager(
            $this->em,
            $this->repository,
            $this->jobRoles,
            $this->skills,
        );
    }

    public function testAssertValidRequirementRejectsDuplicate(): void
    {
        $family = $this->createJobFamily('JFTEST001', 'FIN');
        $grade = $this->createGrade('GRTEST001', 'G2', 2);
        $jobRole = $this->createJobRole('JRTEST001', 'ACC', $family, $grade);
        $skill = $this->createCatalogSkill('SKTEST001', 'EXCEL');
        $existing = (new JobRoleRequiredSkill())
            ->setJobRole($jobRole)
            ->setSkill($skill)
            ->setMinimumLevel('BEGINNER');
        $this->setEntityId($existing, 'JRSTEST001');

        $this->repository->method('findOneByJobRoleAndSkill')->willReturn($existing);

        $this->expectException(InvalidActionInputException::class);
        $this->manager->assertValidRequirement($jobRole, $skill);
    }

    public function testAssertValidRequirementAcceptsNewRequirement(): void
    {
        $family = $this->createJobFamily('JFTEST001', 'FIN');
        $grade = $this->createGrade('GRTEST001', 'G2', 2);
        $jobRole = $this->createJobRole('JRTEST001', 'ACC', $family, $grade);
        $skill = $this->createCatalogSkill('SKTEST001', 'EXCEL');

        $this->repository->method('findOneByJobRoleAndSkill')->willReturn(null);

        $this->manager->assertValidRequirement($jobRole, $skill);

        self::assertTrue(true);
    }

    private function createCatalogSkill(string $id, string $code): Skill
    {
        $category = (new SkillCategory())->setCode('FIN')->setName('Finance');
        $this->setEntityId($category, 'SKCTEST001');

        $skill = (new Skill())
            ->setCode($code)
            ->setName('Excel')
            ->setCategory($category);
        $this->setEntityId($skill, $id);

        return $skill;
    }
}
