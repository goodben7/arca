<?php

namespace App\MessageHandler;

use App\Message\NotifyPayrollMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class NotifyPayrollMessageHandler
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(NotifyPayrollMessage $message): void
    {
        $this->logger->info('payroll notification queued for compensation change', [
            'compensationHistoryId' => $message->getCompensationHistoryId(),
            'employeeId' => $message->getEmployeeId(),
            'oldSalary' => $message->getOldSalary(),
            'newSalary' => $message->getNewSalary(),
            'effectiveDate' => $message->getEffectiveDate()->format('Y-m-d'),
            'reason' => $message->getReason(),
        ]);
    }
}
