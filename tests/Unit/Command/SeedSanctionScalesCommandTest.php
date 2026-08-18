<?php

namespace App\Tests\Unit\Command;

use App\Command\SeedSanctionScalesCommand;
use App\Entity\SanctionScale;
use App\Model\SanctionScaleConstants;
use App\Repository\SanctionScaleRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class SeedSanctionScalesCommandTest extends TestCase
{
    public function testSeedCreatesMissingAndUpdatesExisting(): void
    {
        $existingWarn = (new SanctionScale())
            ->setCode(SanctionScaleConstants::CODE_WARN)
            ->setLabel('Old warning')
            ->setSeverityLevel(1)
            ->setRequiresHearing(false)
            ->setActive(true);

        $repo = $this->createMock(SanctionScaleRepository::class);
        $repo->method('findOneByCode')->willReturnCallback(
            static function (string $code) use ($existingWarn): ?SanctionScale {
                return SanctionScaleConstants::CODE_WARN === $code ? $existingWarn : null;
            }
        );

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->exactly(4))->method('persist')->with(self::isInstanceOf(SanctionScale::class));
        $em->expects($this->once())->method('flush');

        $tester = new CommandTester(new SeedSanctionScalesCommand($em, $repo));
        $status = $tester->execute([]);

        self::assertSame(Command::SUCCESS, $status);
        self::assertStringContainsString('4 created, 1 updated', $tester->getDisplay());
        self::assertSame('Avertissement', $existingWarn->getLabel());
        self::assertSame(2, $existingWarn->getSeverityLevel());
    }
}
