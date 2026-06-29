<?php

namespace App\Model;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Link;
use App\Entity\Employee;
use App\Provider\PromotionEligibilityProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    normalizationContext: ['groups' => 'promotion_eligibility:get'],
    operations: [
        new Get(
            uriTemplate: '/employees/{employeeId}/promotion-eligibility',
            uriVariables: [
                'employeeId' => new Link(fromClass: Employee::class),
            ],
            security: 'is_granted("ROLE_EMPLOYEE_PROMOTION_ELIGIBILITY")',
            provider: PromotionEligibilityProvider::class,
        ),
    ],
)]
class PromotionEligibilityResult
{
    public function __construct(
        #[Groups(['promotion_eligibility:get'])]
        public readonly string $employeeId,
        #[Groups(['promotion_eligibility:get'])]
        public readonly ?string $targetJobRoleId,
        #[Groups(['promotion_eligibility:get'])]
        public readonly bool $eligible,
        /** @var list<string> */
        #[Groups(['promotion_eligibility:get'])]
        public readonly array $reasons = [],
    ) {
    }
}
