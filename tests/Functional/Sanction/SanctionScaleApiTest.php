<?php

namespace App\Tests\Functional\Sanction;

use App\Model\SanctionScaleConstants;
use App\Tests\Functional\AbstractApiTestCase;

class SanctionScaleApiTest extends AbstractApiTestCase
{
    public function testCreateListAndPatchSanctionScale(): void
    {
        $this->createSuperAdminUser('sanction.admin@arca.test');
        $token = $this->authenticate('sanction.admin@arca.test');

        $this->apiRequest(
            'POST',
            '/api/sanction_scales',
            [
                'code' => SanctionScaleConstants::CODE_REPRIMAND,
                'label' => 'Réprimande / Observation',
                'severityLevel' => 1,
                'requiresHearing' => false,
                'active' => true,
            ],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(201);

        $created = $this->decodeJsonResponse();
        self::assertStringStartsWith('SS', $created['id']);
        self::assertSame(SanctionScaleConstants::CODE_REPRIMAND, $created['code']);
        self::assertSame(1, $created['severityLevel']);
        self::assertFalse($created['requiresHearing']);
        self::assertTrue($created['active']);

        $this->apiRequest(
            'GET',
            '/api/sanction_scales?code='.SanctionScaleConstants::CODE_REPRIMAND,
            headers: ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseIsSuccessful();
        $members = $this->getCollectionMembers($this->decodeJsonResponse());
        self::assertCount(1, $members);

        $this->apiRequest(
            'PATCH',
            '/api/sanction_scales/'.$created['id'],
            ['label' => 'Réprimande écrite', 'requiresHearing' => true],
            [
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
                'CONTENT_TYPE' => 'application/merge-patch+json',
            ],
        );
        self::assertResponseIsSuccessful();
        $updated = $this->decodeJsonResponse();
        self::assertSame('Réprimande écrite', $updated['label']);
        self::assertTrue($updated['requiresHearing']);
    }

    public function testCreateWithInvalidSeverityReturns422(): void
    {
        $this->createSuperAdminUser('sanction.invalid@arca.test');
        $token = $this->authenticate('sanction.invalid@arca.test');

        $this->apiRequest(
            'POST',
            '/api/sanction_scales',
            [
                'code' => 'CUSTOM',
                'label' => 'Invalide',
                'severityLevel' => 9,
                'requiresHearing' => false,
                'active' => true,
            ],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );

        self::assertResponseStatusCodeSame(422);
    }

    public function testListRequiresAuthentication(): void
    {
        $this->apiRequest('GET', '/api/sanction_scales');

        self::assertResponseStatusCodeSame(401);
    }
}
