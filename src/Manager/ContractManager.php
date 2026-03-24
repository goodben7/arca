<?php

namespace App\Manager;

use App\Entity\Contract;
use App\Entity\User;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Message\Query\GetUserDetails;
use App\Message\Query\QueryBusInterface;
use App\Model\ActivateContractModel;
use App\Model\CancelContractModel;
use App\Model\ContractConstants;
use App\Model\EndContractModel;
use App\Model\SetContractPendingModel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ContractManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security,
        private QueryBusInterface $queries,
    ) {
    }

    public function activateFrom(ActivateContractModel $model): Contract
    {
        $contract = $this->findContract($model->contractId);

        $this->assertActionAllowed($contract, ContractConstants::ACTION_ACTIVATE);
        $this->applyContractAction($contract, ContractConstants::ACTION_ACTIVATE);
        $this->em->flush();

        return $contract;
    }

    public function endFrom(EndContractModel $model): Contract
    {
        $contract = $this->findContract($model->contractId);

        $this->assertActionAllowed($contract, ContractConstants::ACTION_END);
        $this->applyContractAction($contract, ContractConstants::ACTION_END);
        $this->em->flush();

        return $contract;
    }

    public function cancelFrom(CancelContractModel $model): Contract
    {
        $contract = $this->findContract($model->contractId);

        $this->assertActionAllowed($contract, ContractConstants::ACTION_CANCEL);
        $this->applyContractAction($contract, ContractConstants::ACTION_CANCEL);
        $this->em->flush();

        return $contract;
    }

    public function setPendingFrom(SetContractPendingModel $model): Contract
    {
        $contract = $this->findContract($model->contractId);

        $this->assertActionAllowed($contract, ContractConstants::ACTION_SET_PENDING);
        $this->applyContractAction($contract, ContractConstants::ACTION_SET_PENDING);
        $this->em->flush();

        return $contract;
    }

    private function assertActionAllowed(Contract $contract, string $action): void
    {
        $allowed = ContractConstants::getAllowedActionsForStatus($contract->getStatus());
        if (!in_array($action, $allowed, true)) {
            throw new InvalidActionInputException('Action not allowed : invalid contract state');
        }
    }

    private function applyContractAction(Contract $contract, string $action): void
    {
        $now = new \DateTimeImmutable();
        $actorId = $this->resolveActorId();

        match ($action) {
            ContractConstants::ACTION_ACTIVATE => $contract
                ->setStatus(ContractConstants::STATUS_ACTIVE)
                ->setActivatedAt($now)
                ->setActivatedBy($actorId),
            ContractConstants::ACTION_END => $contract
                ->setStatus(ContractConstants::STATUS_ENDED)
                ->setEndedAt($now)
                ->setEndedBy($actorId)
                ->setEndDate($contract->getEndDate() ?: $now),
            ContractConstants::ACTION_CANCEL => $contract
                ->setStatus(ContractConstants::STATUS_CANCELLED)
                ->setCancelledAt($now)
                ->setCancelledBy($actorId),
            ContractConstants::ACTION_SET_PENDING => $contract
                ->setStatus(ContractConstants::STATUS_PENDING)
                ->setPendingAt($now)
                ->setPendingBy($actorId),
            default => throw new InvalidActionInputException('Action not allowed : unknown action'),
        };
    }

    private function resolveActorId(): string
    {
        $identifier = $this->security->getUser()?->getUserIdentifier();
        if (!$identifier) {
            return 'SYSTEM';
        }

        $user = $this->queries->ask(new GetUserDetails($identifier));

        return $user ? $user->getId() : 'SYSTEM';
    }

    private function findContract(?string $contractId): Contract
    {
        if (!$contractId) {
            throw new InvalidActionInputException('contractId is required');
        }

        $contract = $this->em->find(Contract::class, $contractId);

        if (null === $contract) {
            throw new UnavailableDataException(sprintf('cannot find contract with id: %s', $contractId));
        }

        return $contract;
    }
}
