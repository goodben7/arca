<?php

namespace App\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Employee;
use App\Exception\UnavailableDataException;
use App\Model\DisciplinarySummaryResult;
use App\Repository\DisciplinaryCaseRepository;
use Doctrine\ORM\EntityManagerInterface;

class DisciplinarySummaryProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private DisciplinaryCaseRepository $disciplinaryCases,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): DisciplinarySummaryResult
    {
        $employeeId = $uriVariables['employeeId'] ?? null;

        if (!$employeeId) {
            throw new UnavailableDataException('employeeId is required');
        }

        $employee = $this->em->find(Employee::class, $employeeId);

        if (null === $employee) {
            throw new UnavailableDataException(sprintf('cannot find employee with id: %s', $employeeId));
        }

        $appliedSanctionCount = $this->disciplinaryCases->countAppliedSanctionsForEmployee($employeeId);
        $latestApplied = $this->disciplinaryCases->findLatestAppliedForEmployee($employeeId);
        $scale = $latestApplied?->getSanctionScale();

        return new DisciplinarySummaryResult(
            $employeeId,
            $appliedSanctionCount,
            $this->disciplinaryCases->getMaxSeverityForEmployee($employeeId),
            $scale?->getCode(),
            $scale?->getLabel(),
            $latestApplied?->getAppliedAt(),
            null !== $this->disciplinaryCases->findActiveForEmployee($employeeId),
            $appliedSanctionCount >= 1,
        );
    }
}
