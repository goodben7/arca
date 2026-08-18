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
            ->setLabel('Mise à pied disciplinaire')
            ->setSeverityLevel(4)
            ->setRequiresHearing(true)
            ->setMaxDurationDays(8)
            ->setActive(true);

        $violations = $this->validator->validate($scale);

        self::assertCount(0, $violations);
    }

    public function testSeverityLevelFiveIsAllowedForDismiss(): void
    {
        $scale = (new SanctionScale())
            ->setCode(SanctionScaleConstants::CODE_DISMISS)
            ->setLabel('Licenciement pour faute')
            ->setSeverityLevel(5)
            ->setRequiresHearing(true)
            ->setActive(true);

        $violations = $this->validator->validate($scale);

        self::assertCount(0, $violations);
    }

    public function testSeverityLevelOutOfRangeFailsValidation(): void
    {
        $scale = (new SanctionScale())
            ->setCode(SanctionScaleConstants::CODE_WARN)
            ->setLabel('Avertissement')
            ->setSeverityLevel(6)
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
            ->setLabel('Mise à pied disciplinaire')
            ->setSeverityLevel(4)
            ->setRequiresHearing(true)
            ->setMaxDurationDays(-1)
            ->setActive(true);

        $violations = $this->validator->validate($scale);

        self::assertGreaterThan(0, count($violations));
    }
}
