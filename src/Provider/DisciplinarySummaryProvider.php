<?php

namespace App\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Employee;
use App\Exception\UnavailableDataException;
use App\Model\DisciplinarySummaryResult;
use App\Policy\DisciplinaryRecidivismPolicy;
use App\Repository\DisciplinaryCaseRepository;
use Doctrine\ORM\EntityManagerInterface;

class DisciplinarySummaryProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private DisciplinaryCaseRepository $disciplinaryCases,
        private DisciplinaryRecidivismPolicy $recidivism,
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
        $evaluation = $this->recidivism->evaluateForEmployee($employeeId);

        return new DisciplinarySummaryResult(
            $employeeId,
            $appliedSanctionCount,
            $evaluation->lastSeverityLevel,
            $evaluation->lastSanctionCode,
            $evaluation->lastSanctionLabel,
            $this->disciplinaryCases->findLatestAppliedForEmployee($employeeId)?->getAppliedAt(),
            null !== $this->disciplinaryCases->findActiveForEmployee($employeeId),
            $evaluation->isRepeatOffender,
            $evaluation->requiresAcknowledgement,
            $evaluation->suggestedNextSeverity,
            $evaluation->suggestedNextCode,
            $evaluation->suggestedNextLabel,
            $evaluation->reasons,
        );
    }
}
