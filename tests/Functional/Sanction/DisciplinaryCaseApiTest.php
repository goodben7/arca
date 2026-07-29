<?php

namespace App\Tests\Functional\Sanction;

use App\Entity\SanctionScale;
use App\Model\DisciplinaryCaseConstants;
use App\Model\JourneyEventTypeConstants;
use App\Model\JourneyStageConstants;
use App\Model\SanctionScaleConstants;
use App\Tests\Functional\AbstractApiTestCase;

class DisciplinaryCaseApiTest extends AbstractApiTestCase
{
    public function testWarnHappyPathWithoutHearingAndJourneyOnOpen(): void
    {
        $this->createSuperAdminUser('disciplinary.admin@arca.test');
        $token = $this->authenticate('disciplinary.admin@arca.test');

        $employee = $this->createEmployee('EMP-DISC-001');

        $scale = (new SanctionScale())
            ->setCode(SanctionScaleConstants::CODE_WARN)
            ->setLabel('Avertissement')
            ->setSeverityLevel(1)
            ->setRequiresHearing(false)
            ->setActive(true);
        $this->entityManager->persist($scale);
        $this->entityManager->flush();

        $this->apiRequest(
            'POST',
            '/api/disciplinary_cases',
            [
                'employee' => $employee->getId(),
                'sanctionScale' => $scale->getId(),
                'facts' => 'Retard répété sans justification',
                'occurredAt' => '2026-07-15T10:00:00+00:00',
            ],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(201);
        $created = $this->decodeJsonResponse();
        self::assertStringStartsWith('DS', $created['id']);
        self::assertSame(DisciplinaryCaseConstants::STATUS_DRAFT, $created['status']);
        self::assertSame(SanctionScaleConstants::CODE_WARN, $created['sanctionScale']['code']);
        self::assertFalse($created['sanctionScale']['requiresHearing']);

        $caseId = $created['id'];

        $this->apiRequest(
            'POST',
            '/api/disciplinary_cases/openings',
            ['disciplinaryCaseId' => $caseId],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(200);
        self::assertSame(DisciplinaryCaseConstants::STATUS_OPENED, $this->decodeJsonResponse()['status']);

        $this->apiRequest(
            'GET',
            '/api/employees/'.$employee->getId().'/journey',
            headers: ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseIsSuccessful();
        $entries = $this->getCollectionMembers($this->decodeJsonResponse());
        $started = array_values(array_filter(
            $entries,
            static fn (array $entry): bool => ($entry['eventType'] ?? null) === JourneyEventTypeConstants::DISCIPLINARY_STARTED,
        ));
        self::assertCount(1, $started);
        self::assertSame(JourneyStageConstants::DISCIPLINARY, $started[0]['stage']);

        $this->apiRequest(
            'POST',
            '/api/disciplinary_cases/decisions',
            ['disciplinaryCaseId' => $caseId, 'reason' => 'Avertissement confirmé'],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(200);
        self::assertSame(DisciplinaryCaseConstants::STATUS_DECISION_PENDING, $this->decodeJsonResponse()['status']);

        $this->apiRequest(
            'POST',
            '/api/disciplinary_cases/applications',
            ['disciplinaryCaseId' => $caseId],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(200);
        $applied = $this->decodeJsonResponse();
        self::assertSame(DisciplinaryCaseConstants::STATUS_SANCTION_APPLIED, $applied['status']);
        self::assertArrayHasKey('document', $applied);
        self::assertSame('WARN', $applied['document']['type']);
        self::assertStringStartsWith('DC', $applied['document']['id']);
        self::assertSame($caseId, $applied['document']['documentRefNumber']);

        $this->apiRequest(
            'POST',
            '/api/disciplinary_cases/closures',
            ['disciplinaryCaseId' => $caseId],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(200);
        self::assertSame(DisciplinaryCaseConstants::STATUS_CLOSED, $this->decodeJsonResponse()['status']);
    }

    public function testDisciplinarySummaryAfterClosedCase(): void
    {
        $this->createSuperAdminUser('disciplinary.summary@arca.test');
        $token = $this->authenticate('disciplinary.summary@arca.test');

        $employee = $this->createEmployee('EMP-DISC-SUM-001');

        $scale = (new SanctionScale())
            ->setCode(SanctionScaleConstants::CODE_WARN)
            ->setLabel('Avertissement')
            ->setSeverityLevel(1)
            ->setRequiresHearing(false)
            ->setActive(true);
        $this->entityManager->persist($scale);
        $this->entityManager->flush();

        $this->apiRequest(
            'POST',
            '/api/disciplinary_cases',
            [
                'employee' => $employee->getId(),
                'sanctionScale' => $scale->getId(),
                'facts' => 'Retard répété',
                'occurredAt' => '2026-07-10T10:00:00+00:00',
            ],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(201);
        $caseId = $this->decodeJsonResponse()['id'];

        $this->apiRequest(
            'POST',
            '/api/disciplinary_cases/openings',
            ['disciplinaryCaseId' => $caseId],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(200);

        $this->apiRequest(
            'POST',
            '/api/disciplinary_cases/decisions',
            ['disciplinaryCaseId' => $caseId, 'reason' => 'Avertissement confirmé'],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(200);

        $this->apiRequest(
            'POST',
            '/api/disciplinary_cases/applications',
            ['disciplinaryCaseId' => $caseId],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(200);

        $this->apiRequest(
            'POST',
            '/api/disciplinary_cases/closures',
            ['disciplinaryCaseId' => $caseId],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(200);

        $this->apiRequest(
            'GET',
            '/api/employees/'.$employee->getId().'/disciplinary-summary',
            headers: ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseIsSuccessful();
        $summary = $this->decodeJsonResponse();
        self::assertSame($employee->getId(), $summary['employeeId']);
        self::assertSame(1, $summary['appliedSanctionCount']);
        self::assertSame(1, $summary['maxSeverityLevel']);
        self::assertSame(SanctionScaleConstants::CODE_WARN, $summary['lastSanctionCode']);
        self::assertSame('Avertissement', $summary['lastSanctionLabel']);
        self::assertNotNull($summary['lastAppliedAt']);
        self::assertFalse($summary['hasActiveCase']);
        self::assertTrue($summary['isRepeatOffender']);
    }

    public function testDismissApplyCreatesExitProcess(): void
    {
        $this->createSuperAdminUser('disciplinary.dismiss@arca.test');
        $token = $this->authenticate('disciplinary.dismiss@arca.test');

        $employee = $this->createEmployee('EMP-DISC-DIS-001');

        $scale = (new SanctionScale())
            ->setCode(SanctionScaleConstants::CODE_DISMISS)
            ->setLabel('Licenciement')
            ->setSeverityLevel(4)
            ->setRequiresHearing(true)
            ->setActive(true);
        $this->entityManager->persist($scale);
        $this->entityManager->flush();

        $this->apiRequest(
            'POST',
            '/api/disciplinary_cases',
            [
                'employee' => $employee->getId(),
                'sanctionScale' => $scale->getId(),
                'facts' => 'Faute grave',
                'occurredAt' => '2026-07-12T10:00:00+00:00',
            ],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(201);
        $caseId = $this->decodeJsonResponse()['id'];

        $this->apiRequest(
            'POST',
            '/api/disciplinary_cases/openings',
            ['disciplinaryCaseId' => $caseId],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(200);

        $this->apiRequest(
            'POST',
            '/api/disciplinary_cases/hearings',
            [
                'disciplinaryCaseId' => $caseId,
                'hearingAt' => '2026-07-20T14:00:00+00:00',
            ],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(200);

        $this->apiRequest(
            'POST',
            '/api/disciplinary_cases/decisions',
            ['disciplinaryCaseId' => $caseId, 'reason' => 'Licenciement confirmé'],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(200);

        $this->apiRequest(
            'POST',
            '/api/disciplinary_cases/applications',
            ['disciplinaryCaseId' => $caseId],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(200);
        $applied = $this->decodeJsonResponse();
        self::assertSame(DisciplinaryCaseConstants::STATUS_SANCTION_APPLIED, $applied['status']);
        self::assertArrayHasKey('exitProcess', $applied);
        self::assertSame('DISMISSAL', $applied['exitProcess']['reason']);
        self::assertSame('IN_PROGRESS', $applied['exitProcess']['status']);
        self::assertStringStartsWith('EP', $applied['exitProcess']['id']);
    }

    public function testWarnApplyWithMultipartFile(): void
    {
        $this->createSuperAdminUser('disciplinary.file@arca.test');
        $token = $this->authenticate('disciplinary.file@arca.test');
        $employee = $this->createEmployee('EMP-DISC-FILE');

        $scale = (new SanctionScale())
            ->setCode(SanctionScaleConstants::CODE_WARN)
            ->setLabel('Avertissement')
            ->setSeverityLevel(1)
            ->setRequiresHearing(false)
            ->setActive(true);
        $this->entityManager->persist($scale);
        $this->entityManager->flush();

        $this->apiRequest(
            'POST',
            '/api/disciplinary_cases',
            [
                'employee' => $employee->getId(),
                'sanctionScale' => $scale->getId(),
                'facts' => 'Faits avec lettre',
                'occurredAt' => '2026-07-15T10:00:00+00:00',
            ],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        $caseId = $this->decodeJsonResponse()['id'];

        $this->apiRequest(
            'POST',
            '/api/disciplinary_cases/openings',
            ['disciplinaryCaseId' => $caseId],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        $this->apiRequest(
            'POST',
            '/api/disciplinary_cases/decisions',
            ['disciplinaryCaseId' => $caseId],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );

        $filePath = $this->createTempFile('lettre.pdf', '%PDF-1.4 test letter');
        $this->apiMultipartRequest(
            '/api/disciplinary_cases/applications',
            ['disciplinaryCaseId' => $caseId],
            ['file' => $filePath],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(200);
        $applied = $this->decodeJsonResponse();
        self::assertSame(DisciplinaryCaseConstants::STATUS_SANCTION_APPLIED, $applied['status']);
        self::assertArrayHasKey('document', $applied);
        self::assertSame('WARN', $applied['document']['type']);
        self::assertNotEmpty($applied['document']['filePath'] ?? null);
    }
}
