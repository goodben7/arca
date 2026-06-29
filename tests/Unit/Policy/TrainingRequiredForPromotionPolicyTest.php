<?php

namespace App\Tests\Unit\Policy;

use App\Entity\CareerPath;
use App\Entity\Grade;
use App\Entity\JobRole;
use App\Entity\JobRoleRequiredTraining;
use App\Entity\TrainingCatalog;
use App\Manager\CareerPathManager;
use App\Manager\JobRoleManager;
use App\Model\CareerPathConstants;
use App\Model\EligibilityActionConstants;
use App\Model\EmployeeConstants;
use App\Policy\TrainingRequiredForPromotionPolicy;
use App\Repository\JobRoleRequiredTrainingRepository;
use App\Repository\TrainingEnrollmentRepository;
use App\Tests\Unit\Manager\ManagerTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class TrainingRequiredForPromotionPolicyTest extends ManagerTestCase
{
    private JobRoleManager&MockObject $jobRoles;
    private CareerPathManager&MockObject $careerPaths;
    private JobRoleRequiredTrainingRepository&MockObject $requiredTrainings;
    private TrainingEnrollmentRepository&MockObject $enrollments;
    private TrainingRequiredForPromotionPolicy $policy;

    protected function setUp(): void
    {
        $this->jobRoles = $this->createMock(JobRoleManager::class);
        $this->careerPaths = $this->createMock(CareerPathManager::class);
        $this->requiredTrainings = $this->createMock(JobRoleRequiredTrainingRepository::class);
        $this->enrollments = $this->createMock(TrainingEnrollmentRepository::class);

        $this->policy = new TrainingRequiredForPromotionPolicy(
            $this->jobRoles,
            $this->careerPaths,
            $this->requiredTrainings,
            $this->enrollments,
        );
    }

    public function testSupportsPromotionAction(): void
    {
        self::assertTrue($this->policy->supports(EligibilityActionConstants::PROMOTION));
    }

    public function testEvaluateEligibleWhenAllTrainingsCertified(): void
    {
        $grade = (new Grade())->setCode('G2')->setName('G2')->setRank(2);
        $fromRole = (new JobRole())->setCode('ACC')->setTitle('Comptable')->setGrade($grade);
        $this->setEntityId($fromRole, 'JRTEST001');

        $toRole = (new JobRole())->setCode('ACC-SR')->setTitle('Senior')->setGrade($grade);
        $this->setEntityId($toRole, 'JRTEST002');

        $catalog = (new TrainingCatalog())->setTitle('Excel avancé')->setProvider('Interne')->setDuration(8)->setCost('0');
        $this->setEntityId($catalog, 'TCTEST001');

        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);
        $employee->setJobRole($fromRole);

        $requirement = (new JobRoleRequiredTraining())->setJobRole($toRole)->setCatalogItem($catalog);

        $this->jobRoles->method('find')->willReturn($toRole);
        $this->requiredTrainings->method('findByJobRole')->willReturn([$requirement]);
        $this->careerPaths->method('findByTransition')->willReturn(null);
        $this->enrollments->method('hasCertifiedCatalogForEmployee')->willReturn(true);

        $result = $this->policy->evaluate($employee, ['targetJobRoleId' => 'JRTEST002']);

        self::assertTrue($result->isEligible());
    }

    public function testEvaluateBlocksMissingCertifiedTraining(): void
    {
        $grade = (new Grade())->setCode('G2')->setName('G2')->setRank(2);
        $toRole = (new JobRole())->setCode('ACC-SR')->setTitle('Senior')->setGrade($grade);
        $this->setEntityId($toRole, 'JRTEST002');

        $catalog = (new TrainingCatalog())->setTitle('Sécurité')->setProvider('Externe')->setDuration(4)->setCost('200');
        $this->setEntityId($catalog, 'TCTEST002');

        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);

        $requirement = (new JobRoleRequiredTraining())->setJobRole($toRole)->setCatalogItem($catalog);

        $this->jobRoles->method('find')->willReturn($toRole);
        $this->requiredTrainings->method('findByJobRole')->willReturn([$requirement]);
        $this->enrollments->method('hasCertifiedCatalogForEmployee')->willReturn(false);

        $result = $this->policy->evaluate($employee, ['targetJobRoleId' => 'JRTEST002']);

        self::assertFalse($result->isEligible());
        self::assertStringContainsString('Sécurité', $result->getReasons()[0]);
    }

    public function testEvaluateCareerPathRequiredTrainings(): void
    {
        $grade = (new Grade())->setCode('G1')->setName('G1')->setRank(1);
        $fromRole = (new JobRole())->setCode('ACC-JUNIOR')->setTitle('Junior')->setGrade($grade);
        $this->setEntityId($fromRole, 'JRTEST001');

        $toRole = (new JobRole())->setCode('ACC')->setTitle('Comptable')->setGrade($grade);
        $this->setEntityId($toRole, 'JRTEST002');

        $employee = $this->createEmployee('EMTEST001', EmployeeConstants::STATUS_ACTIVE);
        $employee->setJobRole($fromRole);

        $careerPath = (new CareerPath())
            ->setFromJobRole($fromRole)
            ->setToJobRole($toRole)
            ->setConditions([CareerPathConstants::CONDITION_REQUIRED_TRAININGS => ['TCTEST003']]);

        $this->jobRoles->method('find')->willReturn($toRole);
        $this->requiredTrainings->method('findByJobRole')->willReturn([]);
        $this->careerPaths->method('findByTransition')->willReturn($careerPath);
        $this->enrollments->method('hasCertifiedCatalogForEmployee')->willReturn(false);

        $result = $this->policy->evaluate($employee, ['targetJobRoleId' => 'JRTEST002']);

        self::assertFalse($result->isEligible());
        self::assertStringContainsString('TCTEST003', $result->getReasons()[0]);
    }
}
