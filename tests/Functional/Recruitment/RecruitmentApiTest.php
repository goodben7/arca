<?php

namespace App\Tests\Functional\Recruitment;

use App\Model\ApplicationConstants;
use App\Model\EmployeeConstants;
use App\Model\JobOfferConstants;
use App\Model\OnboardingProcessConstants;
use App\Model\RecruitmentRequestConstants;
use App\Tests\Functional\AbstractApiTestCase;

class RecruitmentApiTest extends AbstractApiTestCase
{
    public function testFullRecruitmentFlowUntilHireCreatesEmployeeAndOnboarding(): void
    {
        $token = $this->authenticateAsHrAdmin();
        $department = $this->createDepartment('REC-IT', 'Recruitment IT');
        $position = $this->createOpenPosition((string) $department->getId(), 'Backend Developer');

        $this->apiRequest(
            'POST',
            '/api/recruitment_requests',
            [
                'department' => $department->getId(),
                'position' => $position->getId(),
                'numberOfPositions' => 1,
                'justification' => 'Besoin équipe produit',
                'description' => 'Poste développeur backend Symfony',
            ],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(201);
        $request = $this->decodeJsonResponse();
        self::assertStringStartsWith('RR', $request['id']);
        self::assertSame(RecruitmentRequestConstants::STATUS_PENDING, $request['status']);

        $this->apiRequest(
            'POST',
            '/api/recruitment_requests/approvals',
            ['recruitmentRequestId' => $request['id']],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(200);
        self::assertSame(
            RecruitmentRequestConstants::STATUS_APPROVED,
            $this->decodeJsonResponse()['status'],
        );

        $this->apiRequest(
            'GET',
            '/api/job_offers?recruitmentRequest='.$request['id'],
            headers: ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseIsSuccessful();
        $offers = $this->getCollectionMembers($this->decodeJsonResponse());
        self::assertCount(1, $offers);
        self::assertSame(JobOfferConstants::STATUS_DRAFT, $offers[0]['status']);
        self::assertSame($request['id'], $offers[0]['recruitmentRequest']);
        $jobOfferId = $offers[0]['id'];

        $this->apiRequest(
            'POST',
            '/api/job_offers/publications',
            ['jobOfferId' => $jobOfferId],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(200);
        self::assertSame(
            JobOfferConstants::STATUS_PUBLISHED,
            $this->decodeJsonResponse()['status'],
        );

        $candidateEmail = 'candidate.hire@arca.test';
        $this->apiRequest(
            'POST',
            '/api/applications',
            [
                'firstName' => 'Alice',
                'lastName' => 'Candidate',
                'email' => $candidateEmail,
                'phone' => '+33600000001',
                'gender' => EmployeeConstants::GENDER_FEMALE,
                'jobOffer' => $jobOfferId,
            ],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(201);
        $application = $this->decodeJsonResponse();
        self::assertStringStartsWith('AP', $application['id']);
        self::assertSame(ApplicationConstants::STATUS_APPLIED, $application['status']);

        $this->apiRequest(
            'POST',
            '/api/applications/shortlistings',
            ['applicationId' => $application['id']],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(200);
        self::assertSame(
            ApplicationConstants::STATUS_SHORTLISTED,
            $this->decodeJsonResponse()['status'],
        );

        $this->apiRequest(
            'POST',
            '/api/applications/interviews',
            ['applicationId' => $application['id']],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(200);
        self::assertSame(
            ApplicationConstants::STATUS_INTERVIEW,
            $this->decodeJsonResponse()['status'],
        );

        $this->apiRequest(
            'POST',
            '/api/applications/hirings',
            ['applicationId' => $application['id']],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(200);
        self::assertSame(
            ApplicationConstants::STATUS_HIRED,
            $this->decodeJsonResponse()['status'],
        );

        $this->apiRequest(
            'GET',
            '/api/employees?email='.$candidateEmail,
            headers: ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseIsSuccessful();
        $employees = $this->getCollectionMembers($this->decodeJsonResponse());
        self::assertCount(1, $employees);
        self::assertSame('Alice', $employees[0]['firstName']);
        self::assertSame('Candidate', $employees[0]['lastName']);
        self::assertSame(EmployeeConstants::STATUS_INACTIVE, $employees[0]['status']);
        self::assertSame($department->getId(), $employees[0]['department']);
        self::assertSame($position->getId(), $employees[0]['position']);
        $employeeId = $employees[0]['id'];

        $this->apiRequest(
            'GET',
            '/api/onboarding_processes?employee='.$employeeId,
            headers: ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseIsSuccessful();
        $processes = $this->getCollectionMembers($this->decodeJsonResponse());
        self::assertCount(1, $processes);
        self::assertSame(OnboardingProcessConstants::STATUS_IN_PROGRESS, $processes[0]['status']);
        self::assertSame($employeeId, $processes[0]['employee']);

        $this->apiRequest(
            'GET',
            '/api/onboarding_tasks?process='.$processes[0]['id'],
            headers: ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseIsSuccessful();
        self::assertCount(5, $this->getCollectionMembers($this->decodeJsonResponse()));
    }

    public function testRejectRecruitmentRequestDoesNotCreateJobOffer(): void
    {
        $token = $this->authenticateAsHrAdmin();
        $department = $this->createDepartment('REC-REJ', 'Recruitment Reject');
        $position = $this->createOpenPosition((string) $department->getId(), 'Rejected Role');

        $this->apiRequest(
            'POST',
            '/api/recruitment_requests',
            [
                'department' => $department->getId(),
                'position' => $position->getId(),
                'numberOfPositions' => 1,
                'justification' => 'Budget insuffisant',
                'description' => 'Demande à rejeter',
            ],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(201);
        $request = $this->decodeJsonResponse();

        $this->apiRequest(
            'POST',
            '/api/recruitment_requests/rejections',
            [
                'recruitmentRequestId' => $request['id'],
                'reason' => 'Pas de budget',
            ],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(200);
        self::assertSame(
            RecruitmentRequestConstants::STATUS_REJECTED,
            $this->decodeJsonResponse()['status'],
        );

        $this->apiRequest(
            'GET',
            '/api/job_offers?recruitmentRequest='.$request['id'],
            headers: ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseIsSuccessful();
        self::assertCount(0, $this->getCollectionMembers($this->decodeJsonResponse()));
    }

    public function testHireFailsFromAppliedStatus(): void
    {
        $token = $this->authenticateAsHrAdmin();
        $jobOfferId = $this->createPublishedJobOffer($token);
        $application = $this->createApplication($token, $jobOfferId, 'early.hire@arca.test');

        $this->apiRequest(
            'POST',
            '/api/applications/hirings',
            ['applicationId' => $application['id']],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );

        self::assertResponseStatusCodeSame(400);
    }

    public function testRejectApplicationFromInterview(): void
    {
        $token = $this->authenticateAsHrAdmin();
        $jobOfferId = $this->createPublishedJobOffer($token);
        $application = $this->createApplication($token, $jobOfferId, 'reject.candidate@arca.test');

        $this->apiRequest(
            'POST',
            '/api/applications/shortlistings',
            ['applicationId' => $application['id']],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(200);

        $this->apiRequest(
            'POST',
            '/api/applications/interviews',
            ['applicationId' => $application['id']],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(200);

        $this->apiRequest(
            'POST',
            '/api/applications/rejections',
            [
                'applicationId' => $application['id'],
                'reason' => 'Profil non adapté',
            ],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(200);
        self::assertSame(
            ApplicationConstants::STATUS_REJECTED,
            $this->decodeJsonResponse()['status'],
        );
    }

    private function authenticateAsHrAdmin(): string
    {
        $this->createEmployeePersonProfile();
        $this->createSuperAdminUser('recruit.admin@arca.test');

        return $this->authenticate('recruit.admin@arca.test');
    }

    private function createPublishedJobOffer(string $token): string
    {
        $department = $this->createDepartment('REC-PUB-'.uniqid(), 'Recruitment Publish');
        $position = $this->createOpenPosition((string) $department->getId(), 'Published Role');

        $this->apiRequest(
            'POST',
            '/api/recruitment_requests',
            [
                'department' => $department->getId(),
                'position' => $position->getId(),
                'numberOfPositions' => 1,
                'justification' => 'Ouverture poste',
                'description' => 'Offre à publier',
            ],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(201);
        $requestId = $this->decodeJsonResponse()['id'];

        $this->apiRequest(
            'POST',
            '/api/recruitment_requests/approvals',
            ['recruitmentRequestId' => $requestId],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(200);

        $this->apiRequest(
            'GET',
            '/api/job_offers?recruitmentRequest='.$requestId,
            headers: ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseIsSuccessful();
        $jobOfferId = $this->getCollectionMembers($this->decodeJsonResponse())[0]['id'];

        $this->apiRequest(
            'POST',
            '/api/job_offers/publications',
            ['jobOfferId' => $jobOfferId],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(200);

        return $jobOfferId;
    }

    /**
     * @return array<string, mixed>
     */
    private function createApplication(string $token, string $jobOfferId, string $email): array
    {
        $this->apiRequest(
            'POST',
            '/api/applications',
            [
                'firstName' => 'Bob',
                'lastName' => 'Applicant',
                'email' => $email,
                'phone' => '+33600000002',
                'gender' => EmployeeConstants::GENDER_MALE,
                'jobOffer' => $jobOfferId,
            ],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(201);

        return $this->decodeJsonResponse();
    }
}
