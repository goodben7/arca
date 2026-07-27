<?php

namespace App\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Employee;
use App\Exception\UnavailableDataException;
use App\Repository\EmployeeJourneyEntryRepository;
use Doctrine\ORM\EntityManagerInterface;

class EmployeeJourneyProvider implements ProviderInterface
{
    public function __construct(
        private EmployeeJourneyEntryRepository $repository,
        private EntityManagerInterface $em,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $employeeId = $uriVariables['employeeId'] ?? null;

        if (!$employeeId) {
            throw new UnavailableDataException('employeeId is required');
        }

        $employee = $this->em->find(Employee::class, $employeeId);

        if (null === $employee) {
            throw new UnavailableDataException(\sprintf('cannot find employee with id: %s', $employeeId));
        }

        return $this->repository->findByEmployeeOrdered($employeeId);
    }
}
