<?php

namespace App\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Employee;
use App\Exception\UnavailableDataException;
use App\Model\EligibilityActionConstants;
use App\Model\PromotionEligibilityResult;
use App\Policy\PolicyEvaluator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class PromotionEligibilityProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private PolicyEvaluator $policyEvaluator,
        private RequestStack $requestStack,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): PromotionEligibilityResult
    {
        $employeeId = $uriVariables['employeeId'] ?? null;

        if (!$employeeId) {
            throw new UnavailableDataException('employeeId is required');
        }

        $employee = $this->em->find(Employee::class, $employeeId);

        if (null === $employee) {
            throw new UnavailableDataException(sprintf('cannot find employee with id: %s', $employeeId));
        }

        $targetJobRoleId = $this->requestStack->getCurrentRequest()?->query->getString('targetJobRole');
        if ('' === $targetJobRoleId) {
            $targetJobRoleId = null;
        }

        $result = $this->policyEvaluator->evaluate(
            EligibilityActionConstants::PROMOTION,
            $employee,
            ['targetJobRoleId' => $targetJobRoleId],
        );

        return new PromotionEligibilityResult(
            $employeeId,
            $targetJobRoleId,
            $result->isEligible(),
            $result->getReasons(),
        );
    }
}
