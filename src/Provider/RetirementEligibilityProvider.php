<?php

namespace App\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Employee;
use App\Exception\UnavailableDataException;
use App\Model\EligibilityActionConstants;
use App\Model\RetirementEligibilityResult;
use App\Policy\PolicyEvaluator;
use Doctrine\ORM\EntityManagerInterface;

class RetirementEligibilityProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private PolicyEvaluator $policyEvaluator,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): RetirementEligibilityResult
    {
        $employeeId = $uriVariables['employeeId'] ?? null;

        if (!$employeeId) {
            throw new UnavailableDataException('employeeId is required');
        }

        $employee = $this->em->find(Employee::class, $employeeId);

        if (null === $employee) {
            throw new UnavailableDataException(sprintf('cannot find employee with id: %s', $employeeId));
        }

        $result = $this->policyEvaluator->evaluate(
            EligibilityActionConstants::RETIREMENT,
            $employee,
        );

        return new RetirementEligibilityResult(
            $employeeId,
            $result->isEligible(),
            $result->getReasons(),
        );
    }
}
