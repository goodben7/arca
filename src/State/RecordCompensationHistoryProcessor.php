<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\RecordCompensationHistoryDto;
use App\Entity\CompensationHistory;
use App\Manager\CompensationHistoryManager;
use App\Model\RecordCompensationHistoryModel;

class RecordCompensationHistoryProcessor implements ProcessorInterface
{
    public function __construct(private CompensationHistoryManager $manager)
    {
    }

    /**
     * @param RecordCompensationHistoryDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): CompensationHistory
    {
        $model = new RecordCompensationHistoryModel(
            $data->employee,
            $data->newSalary,
            $data->effectiveDate,
            $data->reason,
        );

        return $this->manager->recordFrom($model);
    }
}
