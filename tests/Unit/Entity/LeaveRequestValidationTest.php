<?php

namespace App\Tests\Unit\Entity;

use App\Entity\LeaveRequest;
use App\Model\LeaveRequestConstants;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LeaveRequestValidationTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testValidLeaveRequestPassesValidation(): void
    {
        $leaveRequest = $this->createValidLeaveRequest();

        $violations = $this->validator->validate($leaveRequest);

        self::assertCount(0, $violations);
    }

    public function testDefaultStatusIsPending(): void
    {
        $leaveRequest = new LeaveRequest();

        self::assertSame(LeaveRequestConstants::STATUS_PENDING, $leaveRequest->getStatus());
    }

    public function testAllLeaveTypesAreAccepted(): void
    {
        foreach (LeaveRequestConstants::getTypes() as $type) {
            $leaveRequest = $this->createValidLeaveRequest();
            $leaveRequest->setType($type);

            $violations = $this->validator->validate($leaveRequest);

            self::assertCount(0, $violations, sprintf('Type %s should be valid', $type));
        }
    }

    public function testAllStatusesAreAccepted(): void
    {
        foreach (LeaveRequestConstants::getStatuses() as $status) {
            $leaveRequest = $this->createValidLeaveRequest();
            $leaveRequest->setStatus($status);

            $violations = $this->validator->validate($leaveRequest);

            self::assertCount(0, $violations, sprintf('Status %s should be valid', $status));
        }
    }

    public function testMissingEmployeeFailsValidation(): void
    {
        $leaveRequest = $this->createValidLeaveRequest();
        $leaveRequest->setEmployee('');

        $violations = $this->validator->validate($leaveRequest);

        self::assertGreaterThan(0, $violations->count());
    }

    public function testInvalidTypeFailsValidation(): void
    {
        $leaveRequest = $this->createValidLeaveRequest();
        $leaveRequest->setType('INVALID');

        $violations = $this->validator->validate($leaveRequest);

        self::assertGreaterThan(0, $violations->count());
    }

    public function testInvalidStatusFailsValidation(): void
    {
        $leaveRequest = $this->createValidLeaveRequest();
        $leaveRequest->setStatus('UNKNOWN');

        $violations = $this->validator->validate($leaveRequest);

        self::assertGreaterThan(0, $violations->count());
    }

    public function testNonPositiveNumberOfDaysFailsValidation(): void
    {
        $leaveRequest = $this->createValidLeaveRequest();
        $leaveRequest->setNumberOfDays(0);

        $violations = $this->validator->validate($leaveRequest);

        self::assertGreaterThan(0, $violations->count());
    }

    public function testMissingDatesFailValidation(): void
    {
        $leaveRequest = $this->createValidLeaveRequest();

        $startDate = new \ReflectionProperty(LeaveRequest::class, 'startDate');
        $startDate->setValue($leaveRequest, null);
        $endDate = new \ReflectionProperty(LeaveRequest::class, 'endDate');
        $endDate->setValue($leaveRequest, null);

        $violations = $this->validator->validate($leaveRequest);

        self::assertGreaterThanOrEqual(2, $violations->count());
    }

    public function testBuildCreatedAtSetsTimestampOnPersist(): void
    {
        $leaveRequest = $this->createValidLeaveRequest();

        self::assertNull($leaveRequest->getCreatedAt());

        $leaveRequest->buildCreatedAt();

        self::assertInstanceOf(\DateTimeImmutable::class, $leaveRequest->getCreatedAt());
    }

    public function testUpdateUpdatedAtSetsTimestampOnUpdate(): void
    {
        $leaveRequest = $this->createValidLeaveRequest();
        $leaveRequest->setCreatedAt(new \DateTimeImmutable('2025-06-01'));

        self::assertNull($leaveRequest->getUpdatedAt());

        $leaveRequest->updateUpdatedAt();

        self::assertInstanceOf(\DateTimeImmutable::class, $leaveRequest->getUpdatedAt());
    }

    private function createValidLeaveRequest(): LeaveRequest
    {
        return (new LeaveRequest())
            ->setEmployee('EMTEST001')
            ->setType(LeaveRequestConstants::TYPE_ANNUAL)
            ->setStartDate(new \DateTimeImmutable('2025-07-01'))
            ->setEndDate(new \DateTimeImmutable('2025-07-05'))
            ->setNumberOfDays(5)
            ->setReason('family trip');
    }
}
