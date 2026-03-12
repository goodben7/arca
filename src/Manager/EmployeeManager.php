<?php

namespace App\Manager;

use App\Entity\Employee;
use App\Entity\User;
use App\Enum\EntityType;
use App\Event\ActivityEvent;
use App\Exception\UnavailableDataException;
use App\Message\Command\CommandBusInterface;
use App\Message\Command\CreateUserCommand;
use App\Message\Query\GetUserDetails;
use App\Message\Query\QueryBusInterface;
use App\Model\EmployeeConstants;
use App\Model\NewEmployeeModel;
use App\Model\UserProxyIntertace;
use App\Repository\ProfileRepository;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class EmployeeManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ActivityEventDispatcher $eventDispatcher,
        private Security $security,
        private ProfileRepository $profileRepository,
        private QueryBusInterface $queries,
        private CommandBusInterface $bus,
    ) {
    }

    public function generateEmployeeNumber(): string
    {
        $date = (new \DateTime())->format('Ymd');
        $rand = substr(strtoupper(bin2hex(random_bytes(3))), 0, 6);
        return "EMP{$date}{$rand}";
    }

    public function buildFromData(Employee $employee, array $data): Employee
    {
        if (!$employee->getEmployeeNumber()) {
            $employee->setEmployeeNumber($this->generateEmployeeNumber());
        }
        return $employee;
    }

    public function createFrom(NewEmployeeModel $model): Employee
    {
        $userId = $this->security->getUser()?->getUserIdentifier();

        /** @var User|null $user */
        $user = null;
        if ($userId) {
            $user = $this->queries->ask(new GetUserDetails($userId));
        }

        $employee = new Employee();

        $employee
            ->setCreatedBy($user ? $user->getId() : 'SYSTEM') 
            ->setFirstName($model->firstName)
            ->setLastName($model->lastName) 
            ->setEmail($model->email)
            ->setPhone($model->phone)
            ->setGender($model->gender)
            ->setBirthDate($model->birthDate)
            ->setNationality($model->nationality)
            ->setMaritalStatus($model->maritalStatus)
            ->setHireDate($model->hireDate)
            ->setDepartureDate($model->departureDate)
            ->setStatus(EmployeeConstants::STATUS_ACTIVE)
            ->setDepartment($model->department)
            ->setPosition($model->position)
            ->setCreatedAt(new \DateTimeImmutable('now'));

        if ($model->employeeNumber) {
            $employee->setEmployeeNumber($model->employeeNumber);
        } else {
            $employee->setEmployeeNumber($this->generateEmployeeNumber());
        }


        $this->em->persist($employee);

        $profile = $model->profile ?: $this->profileRepository->findOneBy(['personType' => UserProxyIntertace::PERSON_EMPLOYEE]);

        if (null === $profile) {
            throw new UnavailableDataException('cannot find profile with person type: employee');
        }
        
        $user = $this->bus->dispatch(
            new CreateUserCommand(
            $employee->getEmail(),
            $employee->getEmail(),
            $profile,
            $employee->getPhone(),
            $employee->getDisplayName(),
            $employee->getId(),
            EntityType::EMPLOYEE
            )
        );

        $employee->setUserId($user->getId());  

        $this->em->persist($employee);

        $this->em->flush();

        $this->eventDispatcher->dispatch($employee, ActivityEvent::ACTION_CREATE);

        return $employee;
    }
}
