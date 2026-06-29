<?php

namespace App\Policy;

use App\Entity\Employee;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.eligibility_policy')]
interface EligibilityPolicyInterface
{
    public function supports(string $action): bool;

    public function evaluate(Employee $employee, array $context = []): PolicyResult;
}
