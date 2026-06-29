<?php

namespace App\Tests\Functional\Document;

use App\Entity\Document;
use App\Enum\EntityType;
use App\Tests\Functional\AbstractApiTestCase;

class DocumentApiTest extends AbstractApiTestCase
{
    public function testGetCollectionIsAccessibleWithoutAuthentication(): void
    {
        $this->apiRequest('GET', '/api/documents');

        self::assertResponseIsSuccessful();

        $payload = $this->decodeJsonResponse();
        self::assertIsArray($this->getCollectionMembers($payload));
    }

    public function testGetCollectionReturnsEmptyListInitially(): void
    {
        $this->createSuperAdminUser();
        $token = $this->authenticate();

        $this->apiRequest('GET', '/api/documents', headers: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        self::assertResponseIsSuccessful();

        $payload = $this->decodeJsonResponse();
        self::assertSame([], $this->getCollectionMembers($payload));
    }

    public function testPostDocumentWithMultipartCreatesDocument(): void
    {
        $employee = $this->createEmployee();
        $this->createSuperAdminUser();
        $token = $this->authenticate();
        $filePath = $this->createTempFile('contract.pdf', '%PDF-1.4 functional test');

        $this->apiMultipartRequest(
            '/api/documents',
            [
                'type' => Document::TYPE_CONTRACT,
                'holderType' => EntityType::EMPLOYEE,
                'holderId' => (string) $employee->getId(),
                'title' => 'Contrat CDI',
                'documentRefNumber' => 'CNTR-FUNC-001',
            ],
            ['file' => $filePath],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );

        self::assertResponseStatusCodeSame(201);

        $payload = $this->decodeJsonResponse();
        self::assertSame(Document::TYPE_CONTRACT, $payload['type']);
        self::assertSame(EntityType::EMPLOYEE, $payload['holderType']);
        self::assertSame($employee->getId(), $payload['holderId']);
        self::assertSame('Contrat CDI', $payload['title']);
        self::assertSame('CNTR-FUNC-001', $payload['documentRefNumber']);
        self::assertArrayHasKey('id', $payload);
        self::assertStringStartsWith(Document::ID_PREFIX, $payload['id']);
        self::assertArrayHasKey('contentUrl', $payload);
        self::assertNotEmpty($payload['contentUrl']);
        self::assertArrayHasKey('uploadedAt', $payload);
    }

    public function testGetDocumentByIdReturnsCreatedDocument(): void
    {
        $employee = $this->createEmployee();
        $this->createSuperAdminUser();
        $token = $this->authenticate();
        $filePath = $this->createTempFile('cv.pdf');

        $this->apiMultipartRequest(
            '/api/documents',
            [
                'type' => Document::TYPE_CV,
                'holderType' => EntityType::EMPLOYEE,
                'holderId' => (string) $employee->getId(),
                'title' => 'CV candidat',
            ],
            ['file' => $filePath],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );

        self::assertResponseStatusCodeSame(201);
        $created = $this->decodeJsonResponse();

        $this->apiRequest('GET', '/api/documents/'.$created['id'], headers: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        self::assertResponseIsSuccessful();

        $payload = $this->decodeJsonResponse();
        self::assertSame($created['id'], $payload['id']);
        self::assertSame(Document::TYPE_CV, $payload['type']);
        self::assertSame('CV candidat', $payload['title']);
    }

    public function testGetDocumentByIdReturns404ForUnknownId(): void
    {
        $this->createSuperAdminUser();
        $token = $this->authenticate();

        $this->apiRequest('GET', '/api/documents/DCUNKNOWN000001', headers: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        self::assertResponseStatusCodeSame(404);
    }

    public function testGetCollectionCanFilterByHolderId(): void
    {
        $employee = $this->createEmployee('EMP-FUNC-FILTER');
        $otherEmployee = $this->createEmployee('EMP-FUNC-OTHER');
        $this->createSuperAdminUser();
        $token = $this->authenticate();

        foreach ([$employee, $otherEmployee] as $index => $holder) {
            $filePath = $this->createTempFile('doc-'.$index.'.pdf');

            $this->apiMultipartRequest(
                '/api/documents',
                [
                    'type' => Document::TYPE_OTHER,
                    'holderType' => EntityType::EMPLOYEE,
                    'holderId' => (string) $holder->getId(),
                    'title' => 'Doc '.$index,
                ],
                ['file' => $filePath],
                ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
            );
            self::assertResponseStatusCodeSame(201);
        }

        $this->apiRequest(
            'GET',
            '/api/documents?holderId='.$employee->getId(),
            headers: ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );

        self::assertResponseIsSuccessful();

        $payload = $this->decodeJsonResponse();
        self::assertCount(1, $this->getCollectionMembers($payload));
        self::assertSame($employee->getId(), $this->getCollectionMembers($payload)[0]['holderId']);
    }

    public function testPostDocumentWithInvalidTypeReturnsValidationError(): void
    {
        $employee = $this->createEmployee('EMP-FUNC-INVALID');
        $this->createSuperAdminUser();
        $token = $this->authenticate();
        $filePath = $this->createTempFile('bad.pdf');

        $this->apiMultipartRequest(
            '/api/documents',
            [
                'type' => 'INVALID',
                'holderType' => EntityType::EMPLOYEE,
                'holderId' => (string) $employee->getId(),
                'title' => 'Bad type',
            ],
            ['file' => $filePath],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );

        self::assertResponseStatusCodeSame(422);
    }

    public function testDeleteDocumentRequiresDocDeleteRole(): void
    {
        $employee = $this->createEmployee('EMP-FUNC-NODELETE');
        $this->createUserWithPermissions(
            ['ROLE_DOC_LIST', 'ROLE_DOC_DETAILS', 'ROLE_DOC_CREATE'],
            'func-no-delete@arca.test',
        );
        $token = $this->authenticate('func-no-delete@arca.test');
        $filePath = $this->createTempFile('protected.pdf');

        $this->apiMultipartRequest(
            '/api/documents',
            [
                'type' => Document::TYPE_OTHER,
                'holderType' => EntityType::EMPLOYEE,
                'holderId' => (string) $employee->getId(),
                'title' => 'Protected delete',
            ],
            ['file' => $filePath],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );

        self::assertResponseStatusCodeSame(201);
        $created = $this->decodeJsonResponse();

        $this->apiRequest('DELETE', '/api/documents/'.$created['id'], headers: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testDeleteDocumentWithPermissionRemovesDocument(): void
    {
        $employee = $this->createEmployee('EMP-FUNC-DELETE');
        $this->createSuperAdminUser();
        $token = $this->authenticate();
        $filePath = $this->createTempFile('payslip.pdf');

        $this->apiMultipartRequest(
            '/api/documents',
            [
                'type' => Document::TYPE_PAYSLIP,
                'holderType' => EntityType::EMPLOYEE,
                'holderId' => (string) $employee->getId(),
                'title' => 'Fiche de paie juin',
            ],
            ['file' => $filePath],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );

        self::assertResponseStatusCodeSame(201);
        $created = $this->decodeJsonResponse();

        $this->apiRequest('DELETE', '/api/documents/'.$created['id'], headers: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        self::assertResponseStatusCodeSame(204);

        $this->apiRequest('GET', '/api/documents/'.$created['id'], headers: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        self::assertResponseStatusCodeSame(404);
    }
}
