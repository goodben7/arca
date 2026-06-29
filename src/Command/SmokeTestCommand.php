<?php

namespace App\Command;

use App\Entity\Benefit;
use App\Entity\CompensationHistory;
use App\Entity\Employee;
use App\Entity\EmployeeBenefit;
use App\Entity\EmployeeSkill;
use App\Entity\ExitProcess;
use App\Entity\ExitTask;
use App\Entity\MobilityRequest;
use App\Entity\SuccessionPlan;
use App\Manager\CompensationHistoryManager;
use App\Manager\EmployeeBenefitManager;
use App\Manager\ExitProcessManager;
use App\Manager\ExitTaskManager;
use App\Manager\MobilityRequestManager;
use App\Manager\SuccessionPlanManager;
use App\Message\NotifyPayrollMessage;
use App\MessageHandler\NotifyPayrollMessageHandler;
use App\Model\CompensationHistoryConstants;
use App\Model\ContractConstants;
use App\Model\EligibilityActionConstants;
use App\Model\EmployeeBenefitConstants;
use App\Model\EmployeeConstants;
use App\Model\ExitProcessConstants;
use App\Model\JourneyEventTypeConstants;
use App\Model\MobilityRequestConstants;
use App\Model\CompleteExitTaskModel;
use App\Model\NewEmployeeBenefitModel;
use App\Model\NewExitProcessModel;
use App\Model\NewMobilityRequestModel;
use App\Model\NewSuccessionPlanModel;
use App\Model\RecordCompensationHistoryModel;
use App\Model\SuccessionPlanConstants;
use App\Model\StartExitProcessModel;
use App\Model\StartExitTaskModel;
use App\Model\SubmitMobilityRequestModel;
use App\Policy\PolicyEvaluator;
use App\Repository\CompensationHistoryRepository;
use App\Repository\EmployeeJourneyEntryRepository;
use App\Repository\ExitTaskRepository;
use App\Repository\GradeRepository;
use App\Repository\JobRoleRepository;
use App\Repository\SkillRepository;
use App\Service\HrDashboard\HrDashboardCalculator;
use App\Service\SmokeTest\SmokeFixtureFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'ar:smoke:test',
    description: 'Run end-to-end smoke scenarios (mobility, compensation, eligibility, payroll, offboarding, succession, dashboard)',
)]
class SmokeTestCommand extends Command
{
    /** @var list<string> */
    private array $createdEmployeeNumbers = [];

    /** @var list<string> */
    private array $createdBenefitIds = [];

    public function __construct(
        private EntityManagerInterface $em,
        private JobRoleRepository $jobRoles,
        private GradeRepository $grades,
        private SkillRepository $skills,
        private SmokeFixtureFactory $fixtures,
        private MobilityRequestManager $mobilityRequests,
        private CompensationHistoryManager $compensationHistory,
        private EmployeeBenefitManager $employeeBenefits,
        private ExitProcessManager $exitProcesses,
        private ExitTaskManager $exitTasks,
        private ExitTaskRepository $exitTaskRepository,
        private SuccessionPlanManager $successionPlans,
        private HrDashboardCalculator $hrDashboard,
        private PolicyEvaluator $policyEvaluator,
        private EmployeeJourneyEntryRepository $journeyEntries,
        private CompensationHistoryRepository $compensationHistories,
        private NotifyPayrollMessageHandler $payrollNotifier,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('keep-data', null, InputOption::VALUE_NONE, 'Keep smoke test data in database after run')
            ->addOption('fail-fast', null, InputOption::VALUE_NONE, 'Stop on first failing scenario');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Arca — Smoke tests (phases 0–9)');

        if (!$this->assertPrerequisites($io)) {
            return Command::FAILURE;
        }

        $scenarios = [
            'Mobility TRANSFER (workflow + journey)' => fn () => $this->scenarioMobilityTransfer($io),
            'Mobility PROMOTION (workflow + journey + compensation)' => fn () => $this->scenarioMobilityPromotion($io),
            'Promotion eligibility (policy evaluator)' => fn () => $this->scenarioPromotionEligibility($io),
            'Manual compensation history' => fn () => $this->scenarioManualCompensation($io),
            'NotifyPayrollMessage handler' => fn () => $this->scenarioPayrollMessageHandler($io),
            'Offboarding (exit process + benefits + journey)' => fn () => $this->scenarioOffboarding($io),
            'Succession plan + HR dashboard KPIs' => fn () => $this->scenarioSuccessionAndDashboard($io),
        ];

        $passed = 0;
        $failed = 0;

        foreach ($scenarios as $name => $runner) {
            $io->section($name);
            try {
                $runner();
                $io->success('PASS');
                ++$passed;
            } catch (\Throwable $e) {
                $io->error('FAIL: '.$e->getMessage());
                ++$failed;
                if ($input->getOption('fail-fast')) {
                    break;
                }
            }
        }

        if (!$input->getOption('keep-data')) {
            $this->cleanup();
            $io->note('Smoke test data cleaned up (use --keep-data to retain).');
        }

        $io->writeln(sprintf('Results: %d passed, %d failed', $passed, $failed));

        return 0 === $failed ? Command::SUCCESS : Command::FAILURE;
    }

    private function assertPrerequisites(SymfonyStyle $io): bool
    {
        $role = $this->jobRoles->findOneByCode('ACC-JUNIOR');
        if (null === $role) {
            $io->error('Job role ACC-JUNIOR not found. Run: php bin/console app:seed:job-architecture');

            return false;
        }

        if (null === $this->skills->findOneByCode('COMPTA_GEN')) {
            $io->error('Skills catalog not found. Run: php bin/console app:seed:skills');

            return false;
        }

        $io->writeln('Prerequisites OK (job architecture + skills present).');

        return true;
    }

    private function scenarioMobilityTransfer(SymfonyStyle $io): void
    {
        $juniorRole = $this->requireRole('ACC-JUNIOR');
        $gradeG1 = $this->requireGrade('G1');

        ['employee' => $employee] = $this->fixtures->createEmployeeWithActiveContract(
            'transfer',
            $juniorRole,
            $gradeG1,
            'Comptabilité',
            '12000.00',
            new \DateTimeImmutable('2022-01-01'),
        );
        $this->trackEmployee($employee);

        $request = $this->mobilityRequests->createFrom(new NewMobilityRequestModel(
            (string) $employee->getId(),
            MobilityRequestConstants::TYPE_TRANSFER,
            null,
            null,
            'Audit',
            'smoke test transfer',
        ));

        $this->runMobilityWorkflowToImplemented($request);

        $this->em->refresh($employee);
        if ('Audit' !== $employee->getDepartment()) {
            throw new \RuntimeException(sprintf('expected department Audit, got %s', $employee->getDepartment()));
        }

        $this->assertJourneyEvent((string) $employee->getId(), JourneyEventTypeConstants::TRANSFERRED);
        $io->writeln(sprintf('Employee %s transferred to Audit', $employee->getId()));
    }

    private function scenarioMobilityPromotion(SymfonyStyle $io): void
    {
        $juniorRole = $this->requireRole('ACC-JUNIOR');
        $targetRole = $this->requireRole('ACC');
        $gradeG1 = $this->requireGrade('G1');
        $gradeG2 = $this->requireGrade('G2');

        ['employee' => $employee, 'contract' => $contract] = $this->fixtures->createEmployeeWithActiveContract(
            'promotion',
            $juniorRole,
            $gradeG1,
            'Comptabilité',
            '12000.00',
            new \DateTimeImmutable('2022-01-01'),
        );
        $this->trackEmployee($employee);
        $this->fixtures->grantRequiredSkillsForJobRole($employee, $targetRole);

        $request = $this->mobilityRequests->createFrom(new NewMobilityRequestModel(
            (string) $employee->getId(),
            MobilityRequestConstants::TYPE_PROMOTION,
            (string) $targetRole->getId(),
            (string) $gradeG2->getId(),
            null,
            'smoke test promotion',
        ));

        $this->mobilityRequests->submitFrom(new SubmitMobilityRequestModel((string) $request->getId()));
        $this->em->refresh($request);
        $this->runMobilityWorkflowToImplemented($request);

        $this->em->refresh($employee);
        $this->em->refresh($contract);

        if ($targetRole->getId() !== $employee->getJobRole()?->getId()) {
            throw new \RuntimeException('employee job role was not updated after promotion');
        }

        if ($gradeG2->getId() !== $employee->getGrade()?->getId()) {
            throw new \RuntimeException('employee grade was not updated after promotion');
        }

        $this->assertJourneyEvent((string) $employee->getId(), JourneyEventTypeConstants::PROMOTED);

        $history = $this->compensationHistories->findOneBy([
            'employee' => $employee->getId(),
            'sourceEvent' => CompensationHistoryConstants::SOURCE_MOBILITY_IMPLEMENTED,
        ]);

        if (null === $history) {
            throw new \RuntimeException('compensation history not created for promotion');
        }

        if ('24000.00' !== $contract->getSalary()) {
            throw new \RuntimeException(sprintf('expected contract salary 24000.00, got %s', $contract->getSalary()));
        }

        $io->writeln(sprintf(
            'Promotion OK: %s → %s, salary %s → %s',
            $history->getOldSalary(),
            $history->getNewSalary(),
            $history->getOldSalary(),
            $contract->getSalary(),
        ));
    }

    private function scenarioPromotionEligibility(SymfonyStyle $io): void
    {
        $juniorRole = $this->requireRole('ACC-JUNIOR');
        $targetRole = $this->requireRole('ACC');
        $gradeG1 = $this->requireGrade('G1');

        ['employee' => $employee] = $this->fixtures->createEmployeeWithActiveContract(
            'eligibility',
            $juniorRole,
            $gradeG1,
            'Comptabilité',
            '12000.00',
            new \DateTimeImmutable('2022-01-01'),
        );
        $this->trackEmployee($employee);
        $this->fixtures->grantRequiredSkillsForJobRole($employee, $targetRole);

        $result = $this->policyEvaluator->evaluate(
            EligibilityActionConstants::PROMOTION,
            $employee,
            ['targetJobRoleId' => $targetRole->getId()],
        );

        if (!$result->isEligible()) {
            throw new \RuntimeException('expected eligible promotion, reasons: '.implode('; ', $result->getReasons()));
        }

        $io->writeln(sprintf('Eligible for promotion to %s (%s)', $targetRole->getCode(), $targetRole->getId()));
    }

    private function scenarioManualCompensation(SymfonyStyle $io): void
    {
        $juniorRole = $this->requireRole('ACC-JUNIOR');
        $gradeG1 = $this->requireGrade('G1');

        ['employee' => $employee, 'contract' => $contract] = $this->fixtures->createEmployeeWithActiveContract(
            'manual-comp',
            $juniorRole,
            $gradeG1,
            'Comptabilité',
            '12000.00',
            new \DateTimeImmutable('2023-06-01'),
        );
        $this->trackEmployee($employee);

        $history = $this->compensationHistory->recordFrom(new RecordCompensationHistoryModel(
            (string) $employee->getId(),
            '13500.00',
            new \DateTimeImmutable('2026-07-01'),
            'smoke test manual adjustment',
        ));

        $this->em->refresh($contract);

        if (CompensationHistoryConstants::SOURCE_MANUAL !== $history->getSourceEvent()) {
            throw new \RuntimeException('unexpected source event on manual compensation');
        }

        if ('13500.00' !== $contract->getSalary()) {
            throw new \RuntimeException(sprintf('expected contract salary 13500.00, got %s', $contract->getSalary()));
        }

        $io->writeln(sprintf('Manual compensation recorded: %s', $history->getId()));
    }

    private function scenarioPayrollMessageHandler(SymfonyStyle $io): void
    {
        ($this->payrollNotifier)(new NotifyPayrollMessage(
            'CHSMOKE00000001',
            'EMSMOKE00000001',
            '12000.00',
            '13500.00',
            new \DateTimeImmutable('2026-07-01'),
            'smoke test payroll notification',
        ));

        $io->writeln('NotifyPayrollMessageHandler executed without error.');
        $io->writeln('Tip: run `php bin/console messenger:consume async -vv` to process async payroll messages after compensation changes.');
    }

    private function scenarioOffboarding(SymfonyStyle $io): void
    {
        $juniorRole = $this->requireRole('ACC-JUNIOR');
        $gradeG1 = $this->requireGrade('G1');
        $departureDate = new \DateTimeImmutable('2026-08-01');

        ['employee' => $employee, 'contract' => $contract] = $this->fixtures->createEmployeeWithActiveContract(
            'offboarding',
            $juniorRole,
            $gradeG1,
            'Comptabilité',
            '12000.00',
            new \DateTimeImmutable('2022-01-01'),
        );
        $this->trackEmployee($employee);

        $benefit = $this->fixtures->createBenefit('offboarding');
        $this->trackBenefit($benefit);

        $enrollment = $this->employeeBenefits->createFrom(new NewEmployeeBenefitModel(
            (string) $employee->getId(),
            (string) $benefit->getId(),
            new \DateTimeImmutable('2022-01-01'),
        ));

        $process = $this->exitProcesses->createFrom(new NewExitProcessModel(
            (string) $employee->getId(),
            ExitProcessConstants::REASON_RESIGNATION,
            $departureDate,
        ));

        $this->exitProcesses->startFrom(new StartExitProcessModel((string) $process->getId()));
        $this->assertJourneyEvent((string) $employee->getId(), JourneyEventTypeConstants::OFFBOARDING_STARTED);

        $this->em->refresh($process);
        $tasks = $this->exitTaskRepository->findBy(['process' => $process]);
        if ([] === $tasks) {
            throw new \RuntimeException('no exit tasks created after starting offboarding process');
        }

        foreach ($tasks as $task) {
            $this->exitTasks->startFrom(new StartExitTaskModel((string) $task->getId()));
            $this->exitTasks->completeFrom(new CompleteExitTaskModel((string) $task->getId()));
        }

        $this->em->refresh($process);
        $this->em->refresh($employee);
        $this->em->refresh($contract);
        $this->em->refresh($enrollment);

        if (ExitProcessConstants::STATUS_COMPLETED !== $process->getStatus()) {
            throw new \RuntimeException(sprintf('expected exit process COMPLETED, got %s', $process->getStatus()));
        }

        if (EmployeeConstants::STATUS_TERMINATED !== $employee->getStatus()) {
            throw new \RuntimeException(sprintf('expected employee TERMINATED, got %s', $employee->getStatus()));
        }

        if (ContractConstants::STATUS_ENDED !== $contract->getStatus()) {
            throw new \RuntimeException(sprintf('expected contract ENDED, got %s', $contract->getStatus()));
        }

        if (EmployeeBenefitConstants::STATUS_ENDED !== $enrollment->getStatus()) {
            throw new \RuntimeException(sprintf('expected employee benefit ENDED, got %s', $enrollment->getStatus()));
        }

        if ($departureDate->format('Y-m-d') !== $employee->getDepartureDate()?->format('Y-m-d')) {
            throw new \RuntimeException('employee departure date was not set from exit process');
        }

        $this->assertJourneyEvent((string) $employee->getId(), JourneyEventTypeConstants::TERMINATED);
        $this->assertJourneyEvent((string) $employee->getId(), JourneyEventTypeConstants::ARCHIVED);

        $io->writeln(sprintf(
            'Offboarding OK: process %s, %d tasks, employee %s archived',
            $process->getId(),
            \count($tasks),
            $employee->getId(),
        ));
    }

    private function scenarioSuccessionAndDashboard(SymfonyStyle $io): void
    {
        $juniorRole = $this->requireRole('ACC-JUNIOR');
        $cfoRole = $this->requireRole('CFO');
        $gradeG1 = $this->requireGrade('G1');

        ['employee' => $employee] = $this->fixtures->createEmployeeWithActiveContract(
            'succession',
            $juniorRole,
            $gradeG1,
            'Comptabilité',
            '12000.00',
            new \DateTimeImmutable('2022-01-01'),
        );
        $this->trackEmployee($employee);

        $plan = $this->successionPlans->createFrom(new NewSuccessionPlanModel(
            (string) $cfoRole->getId(),
            (string) $employee->getId(),
            SuccessionPlanConstants::READINESS_WITHIN_2_YEARS,
            'smoke test succession',
        ));

        $dashboard = $this->hrDashboard->compute();

        if ($dashboard['headcount'] < 1) {
            throw new \RuntimeException('dashboard headcount should be at least 1');
        }

        if ($dashboard['criticalJobRolesCovered'] < 1) {
            throw new \RuntimeException('dashboard should report at least one covered critical job role');
        }

        if ($dashboard['successionCoveragePercent'] <= 0) {
            throw new \RuntimeException('dashboard succession coverage should be greater than 0');
        }

        $io->writeln(sprintf(
            'Succession OK: plan %s for CFO candidate %s | Dashboard: headcount=%d, coverage=%.1f%%, skillGaps=%d',
            $plan->getId(),
            $employee->getId(),
            $dashboard['headcount'],
            $dashboard['successionCoveragePercent'],
            $dashboard['criticalSkillGaps'],
        ));
    }

    private function runMobilityWorkflowToImplemented(MobilityRequest $request): void
    {
        if (MobilityRequestConstants::STATUS_DRAFT === $request->getStatus()) {
            $this->mobilityRequests->submitFrom(new SubmitMobilityRequestModel((string) $request->getId()));
            $this->em->refresh($request);
        }

        foreach (range(1, 3) as $_) {
            $this->mobilityRequests->approve((string) $request->getId());
            $this->em->refresh($request);
        }

        if (MobilityRequestConstants::STATUS_IMPLEMENTED !== $request->getStatus()) {
            throw new \RuntimeException(sprintf('mobility request not implemented, status=%s', $request->getStatus()));
        }
    }

    private function assertJourneyEvent(string $employeeId, string $eventType): void
    {
        foreach ($this->journeyEntries->findByEmployeeOrdered($employeeId) as $entry) {
            if ($eventType === $entry->getEventType()) {
                return;
            }
        }

        throw new \RuntimeException(sprintf('journey event %s not found for employee %s', $eventType, $employeeId));
    }

    private function requireRole(string $code): \App\Entity\JobRole
    {
        $role = $this->jobRoles->findOneByCode($code);
        if (null === $role) {
            throw new \RuntimeException(sprintf('job role %s not found', $code));
        }

        return $role;
    }

    private function requireGrade(string $code): \App\Entity\Grade
    {
        $grade = $this->grades->findOneByCode($code);
        if (null === $grade) {
            throw new \RuntimeException(sprintf('grade %s not found', $code));
        }

        return $grade;
    }

    private function trackEmployee(Employee $employee): void
    {
        $number = $employee->getEmployeeNumber();
        if (null !== $number) {
            $this->createdEmployeeNumbers[] = $number;
        }
    }

    private function trackBenefit(Benefit $benefit): void
    {
        $id = $benefit->getId();
        if (null !== $id) {
            $this->createdBenefitIds[] = $id;
        }
    }

    private function cleanup(): void
    {
        if ([] === $this->createdEmployeeNumbers) {
            return;
        }

        $employees = $this->em->getRepository(Employee::class)->createQueryBuilder('e')
            ->where('e.employeeNumber IN (:numbers)')
            ->setParameter('numbers', $this->createdEmployeeNumbers)
            ->getQuery()
            ->getResult();

        $employeeIds = array_map(fn (Employee $e) => $e->getId(), $employees);

        if ([] === $employeeIds) {
            return;
        }

        $this->em->createQuery('DELETE FROM '.SuccessionPlan::class.' sp WHERE sp.candidate IN (:ids)')
            ->setParameter('ids', $employeeIds)
            ->execute();

        $this->em->createQuery('DELETE FROM '.ExitTask::class.' xt WHERE xt.process IN (SELECT ep FROM '.ExitProcess::class.' ep WHERE ep.employee IN (:ids))')
            ->setParameter('ids', $employeeIds)
            ->execute();

        $this->em->createQuery('DELETE FROM '.ExitProcess::class.' ep WHERE ep.employee IN (:ids)')
            ->setParameter('ids', $employeeIds)
            ->execute();

        $this->em->createQuery('DELETE FROM '.EmployeeBenefit::class.' eb WHERE eb.employee IN (:ids)')
            ->setParameter('ids', $employeeIds)
            ->execute();

        if ([] !== $this->createdBenefitIds) {
            $this->em->createQuery('DELETE FROM '.Benefit::class.' b WHERE b.id IN (:benefitIds)')
                ->setParameter('benefitIds', $this->createdBenefitIds)
                ->execute();
        }

        $this->em->createQuery('DELETE FROM '.CompensationHistory::class.' ch WHERE ch.employee IN (:ids)')
            ->setParameter('ids', $employeeIds)
            ->execute();

        $this->em->createQuery('DELETE FROM '.MobilityRequest::class.' mr WHERE mr.employee IN (:ids)')
            ->setParameter('ids', $employeeIds)
            ->execute();

        $this->em->createQuery('DELETE FROM '.EmployeeSkill::class.' es WHERE es.employee IN (:ids)')
            ->setParameter('ids', $employeeIds)
            ->execute();

        $this->em->createQuery('DELETE FROM App\Entity\EmployeeJourneyEntry j WHERE j.employee IN (:employees)')
            ->setParameter('employees', $employees)
            ->execute();

        $this->em->createQuery('DELETE FROM App\Entity\Contract c WHERE c.employee IN (:ids)')
            ->setParameter('ids', $employeeIds)
            ->execute();

        $this->em->createQuery('DELETE FROM '.Employee::class.' e WHERE e.id IN (:ids)')
            ->setParameter('ids', $employeeIds)
            ->execute();
    }
}
