<?php

namespace App\Tests\Functional\Retirement;

use App\Model\EmployeeConstants;
use App\Model\JourneyEventTypeConstants;
use App\Model\JourneyStageConstants;
use App\Tests\Functional\AbstractApiTestCase;

class RetirementApiTest extends AbstractApiTestCase
{
    public function testRetirementEligibilityEndpointReturnsFalseForIneligibleEmployee(): void
    {
        $employee = $this->createEmployee('EMP-FUNC-RETIRE-CHECK-NO');
        $employee->setBirthDate((new \DateTimeImmutable())->modify('-50 years'));
        $employee->setHireDate((new \DateTimeImmutable())->modify('-10 years'));
        $this->entityManager->flush();

        $this->createSuperAdminUser('retire.check1@arca.test');
        $token = $this->authenticate('retire.check1@arca.test');

        $this->apiRequest(
            'GET',
            '/api/employees/'.$employee->getId().'/retirement-eligibility',
            headers: ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );

        self::assertResponseIsSuccessful();
        $payload = $this->decodeJsonResponse();
        self::assertSame($employee->getId(), $payload['employeeId']);
        self::assertFalse($payload['eligible']);
        self::assertContains('retirement requires age >= 65 years OR career >= 35 years', $payload['reasons']);
    }

    public function testRetirementEligibilityEndpointReturnsTrueForEligibleEmployee(): void
    {
        $employee = $this->createEmployee('EMP-FUNC-RETIRE-CHECK-YES');
        $employee->setHireDate((new \DateTimeImmutable())->modify('-40 years'));
        $this->entityManager->flush();

        $this->createSuperAdminUser('retire.check2@arca.test');
        $token = $this->authenticate('retire.check2@arca.test');

        $this->apiRequest(
            'GET',
            '/api/employees/'.$employee->getId().'/retirement-eligibility',
            headers: ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );

        self::assertResponseIsSuccessful();
        $payload = $this->decodeJsonResponse();
        self::assertSame($employee->getId(), $payload['employeeId']);
        self::assertTrue($payload['eligible']);
        self::assertSame([], $payload['reasons']);
    }

    public function testRetireReturns400WhenNotEligibleByAgeOrTenure(): void
    {
        $employee = $this->createEmployee('EMP-FUNC-RETIRE-NOTELIGIBLE');
        $employee->setBirthDate((new \DateTimeImmutable())->modify('-50 years'));
        $employee->setHireDate((new \DateTimeImmutable())->modify('-10 years'));
        $this->entityManager->flush();

        $this->createSuperAdminUser('retire.admin1@arca.test');
        $token = $this->authenticate('retire.admin1@arca.test');

        $this->apiRequest(
            'POST',
            '/api/employees/retirements',
            ['employeeId' => $employee->getId()],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );

        self::assertResponseStatusCodeSame(400);
    }

    public function testRetireReturns200WhenEligibleByTenureEvenIfBirthDateMissing(): void
    {
        $employee = $this->createEmployee('EMP-FUNC-RETIRE-ELIGIBLE');
        $employee->setHireDate((new \DateTimeImmutable())->modify('-40 years'));
        // birthDate intentionally left null
        $this->entityManager->flush();

        $this->createSuperAdminUser('retire.admin2@arca.test');
        $token = $this->authenticate('retire.admin2@arca.test');

        $this->apiRequest(
            'POST',
            '/api/employees/retirements',
            ['employeeId' => $employee->getId()],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );

        self::assertResponseStatusCodeSame(200);

        $payload = $this->decodeJsonResponse();
        self::assertSame(EmployeeConstants::STATUS_RETIRED, $payload['status']);

        $this->apiRequest(
            'GET',
            '/api/employees/'.$employee->getId().'/journey',
            headers: ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseIsSuccessful();

        $entries = $this->getCollectionMembers($this->decodeJsonResponse());
        $retiredEntries = array_values(array_filter(
            $entries,
            static fn (array $entry): bool => ($entry['eventType'] ?? null) === JourneyEventTypeConstants::RETIRED,
        ));

        self::assertCount(1, $retiredEntries);
        self::assertSame(JourneyStageConstants::RETIREMENT, $retiredEntries[0]['stage']);
        self::assertSame(JourneyEventTypeConstants::RETIRED, $retiredEntries[0]['eventType']);
    }
}

