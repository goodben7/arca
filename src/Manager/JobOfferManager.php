<?php

namespace App\Manager;

use App\Entity\Department;
use App\Entity\JobOffer;
use App\Entity\RecruitmentRequest;
use App\Event\ActivityEvent;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Model\JobOfferConstants;
use App\Model\PublishJobOfferModel;
use App\Model\CloseJobOfferModel;
use App\Model\SetJobOfferDraftModel;
use App\Model\NewJobOfferModel;
use App\Message\Query\GetUserDetails;
use App\Message\Query\QueryBusInterface;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class JobOfferManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ActivityEventDispatcher $eventDispatcher,
        private Security $security,
        private QueryBusInterface $queries,
    ) {
    }

    public function createFrom(NewJobOfferModel $model): JobOffer
    {
        $department = $this->findDepartment($model->department);
        $recruitmentRequest = $this->findRecruitmentRequest($model->recruitmentRequest);

        $jobOffer = new JobOffer();
        $jobOffer
            ->setTitle($model->title)
            ->setDepartment($department->getId())
            ->setRecruitmentRequest($recruitmentRequest->getId())
            ->setStatus(JobOfferConstants::STATUS_DRAFT)
        ;

        $this->em->persist($jobOffer);
        $this->em->flush();

        $this->eventDispatcher->dispatch($jobOffer, ActivityEvent::ACTION_CREATE);

        return $jobOffer;
    }

    public function publishFrom(PublishJobOfferModel $model): JobOffer
    {
        $offer = $this->findJobOffer($model->jobOfferId);
        $this->assertActionAllowed($offer, JobOfferConstants::ACTION_PUBLISH);
        $this->applyAction($offer, JobOfferConstants::ACTION_PUBLISH);
        $this->em->flush();
        $this->eventDispatcher->dispatch($offer, ActivityEvent::ACTION_EDIT);
        return $offer;
    }

    public function closeFrom(CloseJobOfferModel $model): JobOffer
    {
        $offer = $this->findJobOffer($model->jobOfferId);
        $this->assertActionAllowed($offer, JobOfferConstants::ACTION_CLOSE);
        $this->applyAction($offer, JobOfferConstants::ACTION_CLOSE);
        $this->em->flush();
        $this->eventDispatcher->dispatch($offer, ActivityEvent::ACTION_EDIT);
        return $offer;
    }

    public function setDraftFrom(SetJobOfferDraftModel $model): JobOffer
    {
        $offer = $this->findJobOffer($model->jobOfferId);
        $this->assertActionAllowed($offer, JobOfferConstants::ACTION_SET_DRAFT);
        $this->applyAction($offer, JobOfferConstants::ACTION_SET_DRAFT);
        $this->em->flush();
        $this->eventDispatcher->dispatch($offer, ActivityEvent::ACTION_EDIT);
        return $offer;
    }

    private function assertActionAllowed(JobOffer $offer, string $action): void
    {
        $allowed = JobOfferConstants::getAllowedActionsForStatus($offer->getStatus());
        if (!\in_array($action, $allowed, true)) {
            throw new InvalidActionInputException('Action not allowed : invalid job offer state');
        }
    }

    private function applyAction(JobOffer $offer, string $action): void
    {
        $now = new \DateTimeImmutable();
        $actorId = $this->resolveActorId();

        match ($action) {
            JobOfferConstants::ACTION_PUBLISH => $offer
                ->setStatus(JobOfferConstants::STATUS_PUBLISHED)
                ->setPublishedAt($now)
                ->setPublishedBy($actorId)
                ->setClosedAt(null)
                ->setClosedBy(null),
            JobOfferConstants::ACTION_CLOSE => $offer
                ->setStatus(JobOfferConstants::STATUS_CLOSED)
                ->setClosedAt($now)
                ->setClosedBy($actorId),
            JobOfferConstants::ACTION_SET_DRAFT => $offer
                ->setStatus(JobOfferConstants::STATUS_DRAFT)
                ->setPublishedAt(null)
                ->setPublishedBy(null)
                ->setClosedAt(null)
                ->setClosedBy(null),
            default => throw new InvalidActionInputException('Action not allowed : unknown action'),
        };
    }

    private function findDepartment(?string $departmentId): Department
    {
        if (!$departmentId) {
            throw new InvalidActionInputException('department is required');
        }

        $department = $this->em->find(Department::class, $departmentId);
        if (null === $department) {
            throw new UnavailableDataException(\sprintf('cannot find department with id: %s', $departmentId));
        }
        return $department;
    }

    private function findRecruitmentRequest(?string $recruitmentRequestId): RecruitmentRequest
    {
        if (!$recruitmentRequestId) {
            throw new InvalidActionInputException('recruitmentRequest is required');
        }

        $request = $this->em->find(RecruitmentRequest::class, $recruitmentRequestId);
        if (null === $request) {
            throw new UnavailableDataException(\sprintf('cannot find recruitment request with id: %s', $recruitmentRequestId));
        }
        return $request;
    }

    private function findJobOffer(?string $jobOfferId): JobOffer
    {
        if (!$jobOfferId) {
            throw new InvalidActionInputException('jobOfferId is required');
        }
        $offer = $this->em->find(JobOffer::class, $jobOfferId);
        if (null === $offer) {
            throw new UnavailableDataException(sprintf('cannot find job offer with id: %s', $jobOfferId));
        }
        return $offer;
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
}
