<?php

namespace App\EventListener;

use App\Entity\Employee;
use App\Event\Domain\MobilityImplementedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: MobilityImplementedEvent::class, method: 'onMobilityImplemented', priority: 10)]
class ApplyMobilityOnImplementedListener
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function onMobilityImplemented(MobilityImplementedEvent $event): void
    {
        $request = $event->getMobilityRequest();
        $employee = $this->em->find(Employee::class, $request->getEmployee());

        if (null === $employee) {
            return;
        }

        $targetJobRole = $request->getTargetJobRole();
        if (null !== $targetJobRole) {
            $employee->setJobRole($targetJobRole);
        }

        $targetGrade = $request->getTargetGrade();
        if (null !== $targetGrade) {
            $employee->setGrade($targetGrade);
        } elseif (null !== $targetJobRole && null !== $targetJobRole->getGrade()) {
            $employee->setGrade($targetJobRole->getGrade());
        }

        $targetDepartment = $request->getTargetDepartment();
        if (null !== $targetDepartment) {
            $employee->setDepartment($targetDepartment);
        }

        $this->em->flush();
    }
}
