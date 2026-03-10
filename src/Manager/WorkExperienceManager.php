<?php

namespace App\Manager;

use App\Entity\User;
use App\Entity\WorkExperience;
use App\Event\ActivityEvent;
use App\Message\Query\GetUserDetails;
use App\Message\Query\QueryBusInterface;
use App\Model\NewWorkExperienceModel;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class WorkExperienceManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ActivityEventDispatcher $eventDispatcher,
        private Security $security,
        private QueryBusInterface $queries,
    ) {
    }

    public function createFrom(NewWorkExperienceModel $model): WorkExperience
    {
        $userId = $this->security->getUser()?->getUserIdentifier();
        /** @var User|null $user */
        $user = $userId ? $this->queries->ask(new GetUserDetails($userId)) : null;

        $workExperience = new WorkExperience();
        $workExperience
            ->setEmployee($model->employeeId)
            ->setCompany($model->company)
            ->setPosition($model->position)
            ->setStartDate($model->startDate)
            ->setEndDate($model->endDate)
            ->setDescription($model->description)
            ->setIsInternal((bool) ($model->isInternal ?? false));

        $this->em->persist($workExperience);
        $this->em->flush();

        $this->eventDispatcher->dispatch($workExperience, ActivityEvent::ACTION_CREATE);

        return $workExperience;
    }
}
