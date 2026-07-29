<?php

namespace App\Tests\Unit\Entity;

use App\Entity\SanctionScale;
use App\Model\SanctionScaleConstants;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SanctionScaleValidationTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testValidSanctionScalePassesValidation(): void
    {
        $scale = (new SanctionScale())
            ->setCode(SanctionScaleConstants::CODE_SUSPEND)
            ->setLabel('Mise à pied')
            ->setSeverityLevel(3)
            ->setRequiresHearing(true)
            ->setMaxDurationDays(8)
            ->setActive(true);

        $violations = $this->validator->validate($scale);

        self::assertCount(0, $violations);
    }

    public function testSeverityLevelOutOfRangeFailsValidation(): void
    {
        $scale = (new SanctionScale())
            ->setCode(SanctionScaleConstants::CODE_WARN)
            ->setLabel('Avertissement')
            ->setSeverityLevel(5)
            ->setRequiresHearing(false)
            ->setActive(true);

        $violations = $this->validator->validate($scale);

        self::assertGreaterThan(0, count($violations));
    }

    public function testBlankCodeFailsValidation(): void
    {
        $scale = (new SanctionScale())
            ->setCode('')
            ->setLabel('Avertissement')
            ->setSeverityLevel(1)
            ->setRequiresHearing(false)
            ->setActive(true);

        $violations = $this->validator->validate($scale);

        self::assertGreaterThan(0, count($violations));
    }

    public function testNegativeMaxDurationDaysFailsValidation(): void
    {
        $scale = (new SanctionScale())
            ->setCode(SanctionScaleConstants::CODE_SUSPEND)
            ->setLabel('Mise à pied')
            ->setSeverityLevel(3)
            ->setRequiresHearing(true)
            ->setMaxDurationDays(-1)
            ->setActive(true);

        $violations = $this->validator->validate($scale);

        self::assertGreaterThan(0, count($violations));
    }
}
