<?php

namespace App\Tests\Unit\Policy;

use App\Entity\DisciplinaryCase;
use App\Entity\SanctionScale;
use App\Exception\InvalidActionInputException;
use App\Model\DisciplinaryCaseConstants;
use App\Model\SanctionScaleConstants;
use App\Policy\DisciplinaryRecidivismPolicy;
use App\Repository\DisciplinaryCaseRepository;
use App\Repository\SanctionScaleRepository;
use PHPUnit\Framework\TestCase;

class DisciplinaryRecidivismPolicyTest extends TestCase
{
    public function testFirstSanctionIsAlwaysAllowed(): void
    {
        $warn = $this->scale(SanctionScaleConstants::CODE_WARN, 2);
        $policy = $this->policy(0, null, null, null);

        $evaluation = $policy->evaluateForEmployee('EM1', $warn);

        self::assertFalse($evaluation->isRepeatOffender);
        self::assertTrue($evaluation->allowed);
        self::assertFalse($evaluation->requiresAcknowledgement);
        self::assertSame([], $evaluation->reasons);
    }

    public function testLowerSeverityIsBlocked(): void
    {
        $warn = $this->scale(SanctionScaleConstants::CODE_WARN, 2);
        $blame = $this->scale(SanctionScaleConstants::CODE_BLAME, 3);
        $latest = (new DisciplinaryCase())
            ->setEmployee('EM1')
            ->setSanctionScale($blame)
            ->setFacts('Faits')
            ->setOccurredAt(new \DateTimeImmutable())
            ->setStatus(DisciplinaryCaseConstants::STATUS_CLOSED);

        $policy = $this->policy(1, $latest, 3, $this->scale(SanctionScaleConstants::CODE_SUSPEND, 4));

        $this->expectException(InvalidActionInputException::class);
        $this->expectExceptionMessage('below last applied severity 3');

        $policy->assertAllowed('EM1', $warn);
    }

    public function testSameSeverityRequiresAcknowledgement(): void
    {
        $warn = $this->scale(SanctionScaleConstants::CODE_WARN, 2);
        $blame = $this->scale(SanctionScaleConstants::CODE_BLAME, 3);
        $latest = (new DisciplinaryCase())
            ->setEmployee('EM1')
            ->setSanctionScale($warn)
            ->setFacts('Faits')
            ->setOccurredAt(new \DateTimeImmutable())
            ->setStatus(DisciplinaryCaseConstants::STATUS_CLOSED);

        $policy = $this->policy(1, $latest, 2, $blame);

        $blocked = $policy->evaluateForEmployee('EM1', $warn);
        self::assertTrue($blocked->isRepeatOffender);
        self::assertFalse($blocked->allowed);
        self::assertTrue($blocked->requiresAcknowledgement);
        self::assertSame(SanctionScaleConstants::CODE_BLAME, $blocked->suggestedNextCode);

        $allowed = $policy->evaluateForEmployee('EM1', $warn, true);
        self::assertTrue($allowed->allowed);
        self::assertSame([], $allowed->reasons);
    }

    public function testHigherSeverityIsAllowedWithoutAcknowledgement(): void
    {
        $warn = $this->scale(SanctionScaleConstants::CODE_WARN, 2);
        $blame = $this->scale(SanctionScaleConstants::CODE_BLAME, 3);
        $latest = (new DisciplinaryCase())
            ->setEmployee('EM1')
            ->setSanctionScale($warn)
            ->setFacts('Faits')
            ->setOccurredAt(new \DateTimeImmutable())
            ->setStatus(DisciplinaryCaseConstants::STATUS_CLOSED);

        $policy = $this->policy(1, $latest, 2, $blame);
        $evaluation = $policy->assertAllowed('EM1', $blame);

        self::assertTrue($evaluation->allowed);
        self::assertFalse($evaluation->requiresAcknowledgement);
    }

    public function testSummaryWithoutProposedScaleSuggestsNextTier(): void
    {
        $warn = $this->scale(SanctionScaleConstants::CODE_WARN, 2);
        $blame = $this->scale(SanctionScaleConstants::CODE_BLAME, 3);
        $latest = (new DisciplinaryCase())
            ->setEmployee('EM1')
            ->setSanctionScale($warn)
            ->setFacts('Faits')
            ->setOccurredAt(new \DateTimeImmutable())
            ->setStatus(DisciplinaryCaseConstants::STATUS_CLOSED);

        $policy = $this->policy(1, $latest, 2, $blame);
        $evaluation = $policy->evaluateForEmployee('EM1');

        self::assertTrue($evaluation->isRepeatOffender);
        self::assertTrue($evaluation->allowed);
        self::assertTrue($evaluation->requiresAcknowledgement);
        self::assertSame(SanctionScaleConstants::CODE_BLAME, $evaluation->suggestedNextCode);
        self::assertNotEmpty($evaluation->reasons);
    }

    public function testSameLevelAtMaximumRequiresAcknowledgement(): void
    {
        $dismiss = $this->scale(SanctionScaleConstants::CODE_DISMISS, 5);
        $latest = (new DisciplinaryCase())
            ->setEmployee('EM1')
            ->setSanctionScale($dismiss)
            ->setFacts('Faits')
            ->setOccurredAt(new \DateTimeImmutable())
            ->setStatus(DisciplinaryCaseConstants::STATUS_CLOSED);

        $policy = $this->policy(1, $latest, 5, null);

        $blocked = $policy->evaluateForEmployee('EM1', $dismiss);
        self::assertFalse($blocked->allowed);
        self::assertTrue($blocked->requiresAcknowledgement);
        self::assertNull($blocked->suggestedNextCode);

        $allowed = $policy->assertAllowed('EM1', $dismiss, true);
        self::assertTrue($allowed->allowed);
    }

    private function scale(string $code, int $severity): SanctionScale
    {
        return (new SanctionScale())
            ->setCode($code)
            ->setLabel($code)
            ->setSeverityLevel($severity)
            ->setRequiresHearing($severity >= 3)
            ->setActive(true);
    }

    private function policy(
        int $count,
        ?DisciplinaryCase $latest,
        ?int $maxSeverity,
        ?SanctionScale $suggested,
    ): DisciplinaryRecidivismPolicy {
        $cases = $this->createMock(DisciplinaryCaseRepository::class);
        $cases->method('countAppliedSanctionsForEmployee')->willReturn($count);
        $cases->method('findLatestAppliedForEmployee')->willReturn($latest);
        $cases->method('getMaxSeverityForEmployee')->willReturn($maxSeverity);

        $scales = $this->createMock(SanctionScaleRepository::class);
        $scales->method('findNextActiveByMinSeverity')->willReturn($suggested);

        return new DisciplinaryRecidivismPolicy($cases, $scales);
    }
}
