<?php

namespace App\Tests\Functional\Onboarding;

use App\Model\EmployeeConstants;
use App\Model\OnboardingProcessConstants;
use App\Model\OnboardingTaskConstants;
use App\Tests\Functional\AbstractApiTestCase;

class OnboardingApiTest extends AbstractApiTestCase
{
    public function testCreateEmployeeAutoStartsOnboardingProcessWithChecklist(): void
    {
        $token = $this->authenticateAsHrAdmin();
        $employee = $this->createEmployeeViaApi($token, 'onboard.create@arca.test');

        self::assertSame(EmployeeConstants::STATUS_INACTIVE, $employee['status']);

        $this->apiRequest(
            'GET',
            '/api/onboarding_processes?employee='.$employee['id'],
            headers: ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );

        self::assertResponseIsSuccessful();

        $processes = $this->getCollectionMembers($this->decodeJsonResponse());
        self::assertCount(1, $processes);
        self::assertStringStartsWith('OP', $processes[0]['id']);
        self::assertSame($employee['id'], $processes[0]['employee']);
        self::assertSame(OnboardingProcessConstants::STATUS_IN_PROGRESS, $processes[0]['status']);
        self::assertNotNull($processes[0]['startedAt']);

        $this->apiRequest(
            'GET',
            '/api/onboarding_tasks?process='.$processes[0]['id'],
            headers: ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );

        self::assertResponseIsSuccessful();

        $tasks = $this->getCollectionMembers($this->decodeJsonResponse());
        self::assertCount(5, $tasks);
        self::assertSame(OnboardingTaskConstants::STATUS_PENDING, $tasks[0]['status']);
        self::assertStringStartsWith('OT', $tasks[0]['id']);
    }

    public function testFullOnboardingWorkflowActivatesEmployee(): void
    {
        $token = $this->authenticateAsHrAdmin();
        $employee = $this->createEmployeeViaApi($token, 'onboard.full@arca.test');

        $process = $this->getOnboardingProcessForEmployee($token, $employee['id']);
        $tasks = $this->getOnboardingTasksForProcess($token, $process['id']);

        foreach ($tasks as $task) {
            $this->apiRequest(
                'POST',
                '/api/onboarding_tasks/starts',
                ['onboardingTaskId' => $task['id']],
                ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
            );
            self::assertResponseStatusCodeSame(200);
            self::assertSame(
                OnboardingTaskConstants::STATUS_IN_PROGRESS,
                $this->decodeJsonResponse()['status'],
            );

            $this->apiRequest(
                'POST',
                '/api/onboarding_tasks/completions',
                ['onboardingTaskId' => $task['id']],
                ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
            );
            self::assertResponseStatusCodeSame(200);
            self::assertSame(
                OnboardingTaskConstants::STATUS_COMPLETED,
                $this->decodeJsonResponse()['status'],
            );
        }

        $this->apiRequest(
            'GET',
            '/api/onboarding_processes/'.$process['id'],
            headers: ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseIsSuccessful();
        $completedProcess = $this->decodeJsonResponse();
        self::assertSame(OnboardingProcessConstants::STATUS_COMPLETED, $completedProcess['status']);
        self::assertNotNull($completedProcess['completedAt']);

        $this->apiRequest(
            'GET',
            '/api/employees/'.$employee['id'],
            headers: ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseIsSuccessful();
        self::assertSame(EmployeeConstants::STATUS_ACTIVE, $this->decodeJsonResponse()['status']);
    }

    public function testCompleteProcessFailsWhileTasksRemainOpen(): void
    {
        $token = $this->authenticateAsHrAdmin();
        $employee = $this->createEmployeeViaApi($token, 'onboard.open@arca.test');
        $process = $this->getOnboardingProcessForEmployee($token, $employee['id']);

        $this->apiRequest(
            'POST',
            '/api/onboarding_processes/completions',
            ['onboardingProcessId' => $process['id']],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );

        self::assertResponseStatusCodeSame(400);
    }

    public function testCancelProcessTransitionsToCancelled(): void
    {
        $token = $this->authenticateAsHrAdmin();
        $employee = $this->createEmployeeViaApi($token, 'onboard.cancel@arca.test');
        $process = $this->getOnboardingProcessForEmployee($token, $employee['id']);

        $this->apiRequest(
            'POST',
            '/api/onboarding_processes/cancellations',
            ['onboardingProcessId' => $process['id']],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );

        self::assertResponseStatusCodeSame(200);
        self::assertSame(
            OnboardingProcessConstants::STATUS_CANCELLED,
            $this->decodeJsonResponse()['status'],
        );
    }

    public function testGetProcessByIdReturns404ForUnknownId(): void
    {
        $token = $this->authenticateAsHrAdmin();

        $this->apiRequest(
            'GET',
            '/api/onboarding_processes/OPUNKNOWN000001',
            headers: ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );

        self::assertResponseStatusCodeSame(404);
    }

    public function testOnboardingListRequiresAuthentication(): void
    {
        $this->apiRequest('GET', '/api/onboarding_processes');

        self::assertResponseStatusCodeSame(401);
    }

    public function testOnboardingListRequiresPermission(): void
    {
        $this->createEmployeePersonProfile();
        $this->createUserWithPermissions(
            ['ROLE_EMPLOYEE_LIST'],
            'onboard.noperm@arca.test',
        );
        $token = $this->authenticate('onboard.noperm@arca.test');

        $this->apiRequest(
            'GET',
            '/api/onboarding_processes',
            headers: ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testCancelTaskThenCompleteRemainingTasksAutoCompletesProcess(): void
    {
        $token = $this->authenticateAsHrAdmin();
        $employee = $this->createEmployeeViaApi($token, 'onboard.mix@arca.test');
        $process = $this->getOnboardingProcessForEmployee($token, $employee['id']);
        $tasks = $this->getOnboardingTasksForProcess($token, $process['id']);

        $first = array_shift($tasks);
        self::assertNotNull($first);

        $this->apiRequest(
            'POST',
            '/api/onboarding_tasks/cancellations',
            ['onboardingTaskId' => $first['id']],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseStatusCodeSame(200);
        self::assertSame(
            OnboardingTaskConstants::STATUS_CANCELLED,
            $this->decodeJsonResponse()['status'],
        );

        foreach ($tasks as $task) {
            $this->apiRequest(
                'POST',
                '/api/onboarding_tasks/starts',
                ['onboardingTaskId' => $task['id']],
                ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
            );
            self::assertResponseStatusCodeSame(200);

            $this->apiRequest(
                'POST',
                '/api/onboarding_tasks/completions',
                ['onboardingTaskId' => $task['id']],
                ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
            );
            self::assertResponseStatusCodeSame(200);
        }

        $this->apiRequest(
            'GET',
            '/api/onboarding_processes/'.$process['id'],
            headers: ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );
        self::assertResponseIsSuccessful();
        self::assertSame(
            OnboardingProcessConstants::STATUS_COMPLETED,
            $this->decodeJsonResponse()['status'],
        );
    }

    private function authenticateAsHrAdmin(): string
    {
        $this->createEmployeePersonProfile();
        $this->createSuperAdminUser('onboard.admin@arca.test');

        return $this->authenticate('onboard.admin@arca.test');
    }

    /**
     * @return array<string, mixed>
     */
    private function createEmployeeViaApi(string $token, string $email): array
    {
        $this->apiRequest(
            'POST',
            '/api/employees',
            [
                'firstName' => 'Onboard',
                'lastName' => 'Tester',
                'email' => $email,
                'gender' => EmployeeConstants::GENDER_FEMALE,
                'hireDate' => '2026-07-01',
            ],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );

        self::assertResponseStatusCodeSame(201);

        return $this->decodeJsonResponse();
    }

    /**
     * @return array<string, mixed>
     */
    private function getOnboardingProcessForEmployee(string $token, string $employeeId): array
    {
        $this->apiRequest(
            'GET',
            '/api/onboarding_processes?employee='.$employeeId,
            headers: ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );

        self::assertResponseIsSuccessful();

        $processes = $this->getCollectionMembers($this->decodeJsonResponse());
        self::assertNotEmpty($processes);

        return $processes[0];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function getOnboardingTasksForProcess(string $token, string $processId): array
    {
        $this->apiRequest(
            'GET',
            '/api/onboarding_tasks?process='.$processId,
            headers: ['HTTP_AUTHORIZATION' => 'Bearer '.$token],
        );

        self::assertResponseIsSuccessful();

        $tasks = $this->getCollectionMembers($this->decodeJsonResponse());
        self::assertNotEmpty($tasks);

        return $tasks;
    }
}
