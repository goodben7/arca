<?php

namespace App\Manager;

use App\Entity\Application;
use App\Entity\JobOffer;
use App\Entity\RecruitmentRequest;
use App\Event\ActivityEvent;
use App\Exception\InvalidActionInputException;
use App\Exception\UnavailableDataException;
use App\Message\Query\GetUserDetails;
use App\Message\Query\QueryBusInterface;
use App\Model\ApplicationConstants;
use App\Model\EmployeeConstants;
use App\Model\HireApplicationModel;
use App\Model\NewEmployeeModel;
use App\Model\NewApplicationModel;
use App\Model\RejectApplicationModel;
use App\Model\SetApplicationAppliedModel;
use App\Model\SetApplicationInterviewModel;
use App\Model\ShortlistApplicationModel;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ApplicationManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ActivityEventDispatcher $eventDispatcher,
        private Security $security,
        private QueryBusInterface $queries,
        private EmployeeManager $employees,
    ) {
    }

    public function createFrom(NewApplicationModel $model): Application
    {
        $jobOffer = $this->findJobOffer($model->jobOffer);

        $application = new Application();
        $application
            ->setFirstName($model->firstName)
            ->setLastName($model->lastName)
            ->setGender($model->gender)
            ->setEmail($model->email)
            ->setPhone($model->phone)
            ->setJobOffer($jobOffer->getId())
            ->setStatus(ApplicationConstants::STATUS_APPLIED)
            ->setAppliedAt(new \DateTimeImmutable())
            ->setNotes($model->notes)
        ;

        $this->em->persist($application);
        $this->em->flush();

        $this->eventDispatcher->dispatch($application, ActivityEvent::ACTION_CREATE);

        return $application;
    }

    public function setAppliedFrom(SetApplicationAppliedModel $model): Application
    {
        $application = $this->findApplication($model->applicationId);
        $this->assertActionAllowed($application, ApplicationConstants::ACTION_SET_APPLIED);
        $this->applyAction($application, ApplicationConstants::ACTION_SET_APPLIED);
        $this->em->flush();
        $this->eventDispatcher->dispatch($application, ActivityEvent::ACTION_EDIT);
        return $application;
    }

    public function shortlistFrom(ShortlistApplicationModel $model): Application
    {
        $application = $this->findApplication($model->applicationId);
        $this->assertActionAllowed($application, ApplicationConstants::ACTION_SET_SHORTLISTED);
        $this->applyAction($application, ApplicationConstants::ACTION_SET_SHORTLISTED);
        $this->em->flush();
        $this->eventDispatcher->dispatch($application, ActivityEvent::ACTION_EDIT);
        return $application;
    }

    public function setInterviewFrom(SetApplicationInterviewModel $model): Application
    {
        $application = $this->findApplication($model->applicationId);
        $this->assertActionAllowed($application, ApplicationConstants::ACTION_SET_INTERVIEW);
        $this->applyAction($application, ApplicationConstants::ACTION_SET_INTERVIEW);
        $this->em->flush();
        $this->eventDispatcher->dispatch($application, ActivityEvent::ACTION_EDIT);
        return $application;
    }

    public function rejectFrom(RejectApplicationModel $model): Application
    {
        $application = $this->findApplication($model->applicationId);
        $this->assertActionAllowed($application, ApplicationConstants::ACTION_REJECT);
        $this->applyAction($application, ApplicationConstants::ACTION_REJECT, $model->reason);
        $this->em->flush();
        $this->eventDispatcher->dispatch($application, ActivityEvent::ACTION_EDIT);
        return $application;
    }

    public function hireFrom(HireApplicationModel $model): Application
    {
        $application = $this->findApplication($model->applicationId);
        $this->assertActionAllowed($application, ApplicationConstants::ACTION_HIRE);
        $this->applyAction($application, ApplicationConstants::ACTION_HIRE);

        $this->employees->createFrom($this->buildEmployeeModelFromApplication($application));

        $this->eventDispatcher->dispatch($application, ActivityEvent::ACTION_EDIT);
        return $application;
    }

    private function findJobOffer(?string $jobOfferId): JobOffer
    {
        if (!$jobOfferId) {
            throw new InvalidActionInputException('jobOffer is required');
        }

        $jobOffer = $this->em->find(JobOffer::class, $jobOfferId);

        if (null === $jobOffer) {
            throw new UnavailableDataException(sprintf('cannot find job offer with id: %s', $jobOfferId));
        }

        return $jobOffer;
    }

    private function findRecruitmentRequest(?string $recruitmentRequestId): RecruitmentRequest
    {
        if (!$recruitmentRequestId) {
            throw new InvalidActionInputException('recruitmentRequestId is required');
        }

        $request = $this->em->find(RecruitmentRequest::class, $recruitmentRequestId);

        if (null === $request) {
            throw new UnavailableDataException(sprintf('cannot find recruitment request with id: %s', $recruitmentRequestId));
        }

        return $request;
    }

    private function findApplication(?string $applicationId): Application
    {
        if (!$applicationId) {
            throw new InvalidActionInputException('applicationId is required');
        }

        $application = $this->em->find(Application::class, $applicationId);

        if (null === $application) {
            throw new UnavailableDataException(sprintf('cannot find application with id: %s', $applicationId));
        }

        return $application;
    }

    private function buildEmployeeModelFromApplication(Application $application): NewEmployeeModel
    {
        $jobOffer = $this->findJobOffer($application->getJobOffer());
        $recruitmentRequest = $this->findRecruitmentRequest($jobOffer->getRecruitmentRequest());

        $hireDate = $application->getHiredAt() ?: new \DateTimeImmutable();

        return new NewEmployeeModel(
            $application->getFirstName(),
            $application->getLastName(),
            $application->getGender() ?: EmployeeConstants::GENDER_OTHER,
            $hireDate,
            $application->getEmail(),
            $application->getPhone(),
            null,
            null,
            null,
            null,
            $jobOffer->getDepartment(),
            $recruitmentRequest->getPosition(),
            null,
            null,
            null,
        );
    }

    private function assertActionAllowed(Application $application, string $action): void
    {
        $allowed = ApplicationConstants::getAllowedActionsForStatus($application->getStatus());
        if (!\in_array($action, $allowed, true)) {
            throw new InvalidActionInputException('Action not allowed : invalid application state');
        }
    }

    private function applyAction(Application $application, string $action, ?string $reason = null): void
    {
        $now = new \DateTimeImmutable();
        $actorId = $this->resolveActorId();

        match ($action) {
            ApplicationConstants::ACTION_SET_APPLIED => $application
                ->setStatus(ApplicationConstants::STATUS_APPLIED)
                ->setShortlistedAt(null)
                ->setShortlistedBy(null)
                ->setInterviewAt(null)
                ->setInterviewBy(null)
                ->setRejectedAt(null)
                ->setRejectedBy(null)
                ->setRejectionReason(null)
                ->setHiredAt(null)
                ->setHiredBy(null),
            ApplicationConstants::ACTION_SET_SHORTLISTED => $application
                ->setStatus(ApplicationConstants::STATUS_SHORTLISTED)
                ->setShortlistedAt($now)
                ->setShortlistedBy($actorId)
                ->setInterviewAt(null)
                ->setInterviewBy(null)
                ->setRejectedAt(null)
                ->setRejectedBy(null)
                ->setRejectionReason(null)
                ->setHiredAt(null)
                ->setHiredBy(null),
            ApplicationConstants::ACTION_SET_INTERVIEW => $application
                ->setStatus(ApplicationConstants::STATUS_INTERVIEW)
                ->setInterviewAt($now)
                ->setInterviewBy($actorId)
                ->setRejectedAt(null)
                ->setRejectedBy(null)
                ->setRejectionReason(null)
                ->setHiredAt(null)
                ->setHiredBy(null),
            ApplicationConstants::ACTION_REJECT => $application
                ->setStatus(ApplicationConstants::STATUS_REJECTED)
                ->setRejectedAt($now)
                ->setRejectedBy($actorId)
                ->setRejectionReason($reason)
                ->setHiredAt(null)
                ->setHiredBy(null),
            ApplicationConstants::ACTION_HIRE => $application
                ->setStatus(ApplicationConstants::STATUS_HIRED)
                ->setHiredAt($now)
                ->setHiredBy($actorId),
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
}
