<?php

namespace App\Manager;

use App\Entity\Position;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Message\Query\GetUserDetails;
use App\Message\Query\QueryBusInterface;
use App\Model\ClosePositionModel;
use App\Model\OpenPositionModel;
use App\Model\PositionStatusConstants;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class PositionManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security,
        private QueryBusInterface $queries,
    ) {
    }

    public function openFrom(OpenPositionModel $model): Position
    {
        $position = $this->findPosition($model->positionId);
        $this->assertActionAllowed($position, PositionStatusConstants::ACTION_OPEN);
        $this->applyAction($position, PositionStatusConstants::ACTION_OPEN);
        $this->em->flush();
        return $position;
    }

    public function closeFrom(ClosePositionModel $model): Position
    {
        $position = $this->findPosition($model->positionId);
        $this->assertActionAllowed($position, PositionStatusConstants::ACTION_CLOSE);
        $this->applyAction($position, PositionStatusConstants::ACTION_CLOSE);
        $this->em->flush();
        return $position;
    }

    private function assertActionAllowed(Position $position, string $action): void
    {
        $allowed = PositionStatusConstants::getAllowedActionsForStatus($position->getStatus());
        if (!in_array($action, $allowed, true)) {
            throw new InvalidActionInputException('Action not allowed : invalid position state');
        }
    }

    private function applyAction(Position $position, string $action): void
    {
        $now = new \DateTimeImmutable();
        $actorId = $this->resolveActorId();

        match ($action) {
            PositionStatusConstants::ACTION_OPEN => $position
                ->setStatus(PositionStatusConstants::STATUS_OPEN)
                ->setOpenedAt($now)
                ->setOpenedBy($actorId),
            PositionStatusConstants::ACTION_CLOSE => $position
                ->setStatus(PositionStatusConstants::STATUS_CLOSED)
                ->setClosedAt($now)
                ->setClosedBy($actorId),
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

    private function findPosition(?string $positionId): Position
    {
        if (!$positionId) {
            throw new InvalidActionInputException('positionId is required');
        }

        $position = $this->em->find(Position::class, $positionId);
        if (null === $position) {
            throw new UnavailableDataException(sprintf('cannot find position with id: %s', $positionId));
        }
        return $position;
    }
}

