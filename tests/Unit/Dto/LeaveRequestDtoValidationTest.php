<?php

namespace App\Tests\Unit\Dto;

use App\Dto\ApproveLeaveRequestDto;
use App\Dto\RejectLeaveRequestDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LeaveRequestDtoValidationTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testApproveDtoRequiresLeaveRequestId(): void
    {
        $dto = new ApproveLeaveRequestDto();
        $dto->leaveRequestId = '';

        $violations = $this->validator->validate($dto);

        self::assertGreaterThan(0, $violations->count());
    }

    public function testApproveDtoAcceptsValidInput(): void
    {
        $dto = new ApproveLeaveRequestDto();
        $dto->leaveRequestId = 'LRTEST001';

        $violations = $this->validator->validate($dto);

        self::assertCount(0, $violations);
    }

    public function testRejectDtoRequiresLeaveRequestIdAndRaison(): void
    {
        $dto = new RejectLeaveRequestDto();
        $dto->leaveRequestId = '';
        $dto->raison = '';

        $violations = $this->validator->validate($dto);

        self::assertGreaterThanOrEqual(2, $violations->count());
    }

    public function testRejectDtoAcceptsValidInput(): void
    {
        $dto = new RejectLeaveRequestDto();
        $dto->leaveRequestId = 'LRTEST001';
        $dto->raison = 'understaffed';

        $violations = $this->validator->validate($dto);

        self::assertCount(0, $violations);
    }
}
