<?php

namespace App\Tests\Unit\Policy;

use App\Entity\CareerPath;
use App\Entity\EmployeeSkill;
use App\Entity\EvaluationCycle;
use App\Entity\Grade;
use App\Entity\JobRole;
use App\Entity\JobRoleRequiredSkill;
use App\Entity\PerformanceReview;
use App\Entity\Skill;
use App\Entity\SkillCategory;
use App\Manager\CareerPathManager;
use App\Manager\JobRoleManager;
use App\Model\CareerPathConstants;
use App\Model\EligibilityActionConstants;
use App\Model\EmployeeConstants;
use App\Model\PerformanceReviewConstants;
use App\Model\SkillConstants;
use App\Policy\PromotionEligibilityPolicy;
use App\Repository\EmployeeSkillRepository;
use App\Repository\JobRoleRequiredSkillRepository;
use App\Repository\PerformanceReviewRepository;
use App\Tests\Unit\Manager\ManagerTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class PromotionEligibilityPolicyTest extends ManagerTestCase
{
    private CareerPathManager&MockObject $careerPaths;
    private JobRoleManager&MockObject $jobRoles;
    private PerformanceReviewRepository&MockObject $performanceReviews;
    private JobRoleRequiredSkillRepository&MockObject $requiredSkills;
    private EmployeeSkillRepository&MockObject $employeeSkills;
    private PromotionEligibilityPolicy $policy;

    protected function setUp(): void
    {
        $this->careerPaths = $this->createMock(CareerPathManager::class);
        $this->jobRoles = $this->createMock(JobRoleManager::class);
        $this->performanceReviews = $this->createMock(PerformanceReviewRepository::class);
        $this->requiredSkills = $this->createMock(JobRoleRequiredSkillRepository::class);
        $this->employeeSkills = $this->createMock(EmployeeSkillRepository::class);

        $this->policy = new PromotionEligibilityPolicy(
            $this->careerPaths,
            $this->jobRoles,
            $this->performanceReviews,
            $this->requiredSkills,
            $this->employeeSkills,
        );
    }

    public function testSupportsPromotionAction(): void
    {
        self::assertTrue($this->policy->supports(EligibilityActionConstants::PROMOTION));
        self::assertFalse($this->policy->supports('TRANSFER'));
    }

    public function testEvaluateRequiresTargetJobRole(): void
    {
        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);

        $result = $this->policy->evaluate($employee, []);

        self::assertFalse($result->isEligible());
        self::assertContains('targetJobRoleId is required', $result->getReasons());
    }

    public function testEvaluateEligibleWhenAllConditionsMet(): void
    {
        $grade = (new Grade())->setCode('G1')->setName('G1')->setRank(1);
        $this->setEntityId($grade, 'GRTEST001');

        $fromRole = (new JobRole())->setCode('ACC-JUNIOR')->setTitle('Junior')->setGrade($grade);
        $this->setEntityId($fromRole, 'JRTEST001');

        $toRole = (new JobRole())->setCode('ACC')->setTitle('Comptable')->setGrade($grade);
        $this->setEntityId($toRole, 'JRTEST002');

        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);
        $employee->setJobRole($fromRole)->setHireDate(new \DateTimeImmutable('-2 years'));

        $careerPath = (new CareerPath())
            ->setFromJobRole($fromRole)
            ->setToJobRole($toRole)
            ->setConditions(['minTenureMonths' => 12, CareerPathConstants::CONDITION_MINIMUM_PERFORMANCE => 3]);

        $cycle = (new EvaluationCycle())->setName('2026')->setYear(2026)
            ->setStartDate(new \DateTimeImmutable('2026-01-01'))
            ->setEndDate(new \DateTimeImmutable('2026-12-31'))
            ->setStatus('OPEN');

        $review = (new PerformanceReview())
            ->setEmployee('EMTEST001')
            ->setCycle($cycle)
            ->setScore('4.00')
            ->setStatus(PerformanceReviewConstants::STATUS_VALIDATED)
            ->setValidatedAt(new \DateTimeImmutable());

        $this->jobRoles->method('find')->with('JRTEST002')->willReturn($toRole);
        $this->careerPaths->method('findByTransition')->willReturn($careerPath);
        $this->performanceReviews->method('findLatestValidatedForEmployee')->willReturn($review);
        $this->requiredSkills->method('findByJobRole')->willReturn([]);

        $result = $this->policy->evaluate($employee, ['targetJobRoleId' => 'JRTEST002']);

        self::assertTrue($result->isEligible());
        self::assertSame([], $result->getReasons());
    }

    public function testEvaluateBlocksWhenSkillLevelInsufficient(): void
    {
        $grade = (new Grade())->setCode('G2')->setName('G2')->setRank(2);
        $this->setEntityId($grade, 'GRTEST001');

        $fromRole = (new JobRole())->setCode('ACC')->setTitle('Comptable')->setGrade($grade);
        $this->setEntityId($fromRole, 'JRTEST001');

        $toRole = (new JobRole())->setCode('ACC-SR')->setTitle('Senior')->setGrade($grade);
        $this->setEntityId($toRole, 'JRTEST002');

        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);
        $employee->setJobRole($fromRole)->setHireDate(new \DateTimeImmutable('-5 years'));

        $category = (new SkillCategory())->setCode('FIN')->setName('Finance');
        $this->setEntityId($category, 'SKCTEST01');

        $skill = (new Skill())->setCode('EXCEL')->setName('Excel')->setCategory($category);
        $this->setEntityId($skill, 'SKTEST001');

        $requirement = (new JobRoleRequiredSkill())
            ->setJobRole($toRole)
            ->setSkill($skill)
            ->setMinimumLevel(SkillConstants::LEVEL_ADVANCED);

        $employeeSkill = (new EmployeeSkill())
            ->setEmployee('EMTEST001')
            ->setSkill($skill)
            ->setLevel(SkillConstants::LEVEL_INTERMEDIATE)
            ->setValidatedAt(new \DateTimeImmutable());

        $careerPath = (new CareerPath())
            ->setFromJobRole($fromRole)
            ->setToJobRole($toRole)
            ->setConditions(['minTenureMonths' => 12]);

        $this->jobRoles->method('find')->willReturn($toRole);
        $this->careerPaths->method('findByTransition')->willReturn($careerPath);
        $this->performanceReviews->method('findLatestValidatedForEmployee')->willReturn(null);
        $this->requiredSkills->method('findByJobRole')->willReturn([$requirement]);
        $this->employeeSkills->method('findOneByEmployeeAndSkill')->willReturn($employeeSkill);

        $result = $this->policy->evaluate($employee, ['targetJobRoleId' => 'JRTEST002']);

        self::assertFalse($result->isEligible());
        self::assertNotEmpty($result->getReasons());
    }
}
