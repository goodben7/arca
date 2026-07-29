<?php

namespace App\Model;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Link;
use App\Entity\Employee;
use App\Provider\RetirementEligibilityProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    normalizationContext: ['groups' => 'retirement_eligibility:get'],
    operations: [
        new Get(
            uriTemplate: '/employees/{employeeId}/retirement-eligibility',
            uriVariables: [
                'employeeId' => new Link(fromClass: Employee::class),
            ],
            security: 'is_granted("ROLE_EMPLOYEE_RETIRE")',
            provider: RetirementEligibilityProvider::class,
        ),
    ],
)]
class RetirementEligibilityResult
{
    /**
     * @param list<string> $reasons
     */
    public function __construct(
        #[Groups(['retirement_eligibility:get'])]
        public readonly string $employeeId,
        #[Groups(['retirement_eligibility:get'])]
        public readonly bool $eligible,
        #[Groups(['retirement_eligibility:get'])]
        public readonly array $reasons = [],
    ) {
    }
}
