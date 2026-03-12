<?php

namespace App\Command;

use App\Entity\Department;
use App\Entity\Position;
use App\Model\PositionLevel;
use App\Model\PositionStatusConstants;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'ar:seed:positions',
    description: 'Seed positions',
)]
class SeedPositionsCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $departmentRepo = $this->em->getRepository(Department::class);
        $departmentIdsByCode = [];
        foreach (['HR', 'IT', 'FIN', 'OPS', 'SALES'] as $code) {
            /** @var Department|null $department */
            $department = $departmentRepo->findOneBy(['code' => $code]);
            if (!$department?->getId()) {
                $output->writeln(sprintf('Seed aborted: département introuvable pour code=%s. Lance ar:seed:departments avant.', $code));
                return Command::FAILURE;
            }
            $departmentIdsByCode[$code] = $department->getId();
        }

        $specs = [
            [
                'title' => 'Responsable RH',
                'departmentCode' => 'HR',
                'level' => PositionLevel::MANAGER,
                'description' => 'Pilotage des opérations RH et management du département',
                'headcount' => 1,
                'openPositions' => 0,
                'status' => PositionStatusConstants::STATUS_OPEN,
            ],
            [
                'title' => 'Gestionnaire Paie',
                'departmentCode' => 'HR',
                'level' => PositionLevel::MID_LEVEL,
                'description' => 'Traitement de la paie, déclarations et suivi administratif',
                'headcount' => 2,
                'openPositions' => 1,
                'status' => PositionStatusConstants::STATUS_OPEN,
            ],
            [
                'title' => 'Responsable Informatique',
                'departmentCode' => 'IT',
                'level' => PositionLevel::MANAGER,
                'description' => 'Supervision SI, applications, support et sécurité',
                'headcount' => 1,
                'openPositions' => 0,
                'status' => PositionStatusConstants::STATUS_OPEN,
            ],
            [
                'title' => 'Développeur Backend',
                'departmentCode' => 'IT',
                'level' => PositionLevel::MID_LEVEL,
                'description' => 'Développement API et services backend',
                'headcount' => 4,
                'openPositions' => 1,
                'status' => PositionStatusConstants::STATUS_OPEN,
            ],
            [
                'title' => 'Technicien Support',
                'departmentCode' => 'IT',
                'level' => PositionLevel::JUNIOR,
                'description' => 'Support utilisateurs et maintenance de premier niveau',
                'headcount' => 3,
                'openPositions' => 0,
                'status' => PositionStatusConstants::STATUS_OPEN,
            ],
            [
                'title' => 'Responsable Finance',
                'departmentCode' => 'FIN',
                'level' => PositionLevel::MANAGER,
                'description' => 'Comptabilité, trésorerie, budget et contrôle interne',
                'headcount' => 1,
                'openPositions' => 0,
                'status' => PositionStatusConstants::STATUS_OPEN,
            ],
            [
                'title' => 'Comptable',
                'departmentCode' => 'FIN',
                'level' => PositionLevel::MID_LEVEL,
                'description' => 'Saisie comptable, rapprochements et reporting',
                'headcount' => 3,
                'openPositions' => 0,
                'status' => PositionStatusConstants::STATUS_OPEN,
            ],
            [
                'title' => 'Responsable Opérations',
                'departmentCode' => 'OPS',
                'level' => PositionLevel::MANAGER,
                'description' => 'Exploitation, logistique et amélioration continue',
                'headcount' => 1,
                'openPositions' => 0,
                'status' => PositionStatusConstants::STATUS_OPEN,
            ],
            [
                'title' => 'Coordinateur Logistique',
                'departmentCode' => 'OPS',
                'level' => PositionLevel::MID_LEVEL,
                'description' => 'Coordination des flux, suivi des livraisons et stocks',
                'headcount' => 2,
                'openPositions' => 1,
                'status' => PositionStatusConstants::STATUS_OPEN,
            ],
            [
                'title' => 'Responsable Commercial',
                'departmentCode' => 'SALES',
                'level' => PositionLevel::MANAGER,
                'description' => 'Ventes, partenariats et relation client',
                'headcount' => 1,
                'openPositions' => 0,
                'status' => PositionStatusConstants::STATUS_OPEN,
            ],
            [
                'title' => 'Commercial',
                'departmentCode' => 'SALES',
                'level' => PositionLevel::MID_LEVEL,
                'description' => 'Prospection, portefeuille clients et suivi des ventes',
                'headcount' => 4,
                'openPositions' => 2,
                'status' => PositionStatusConstants::STATUS_OPEN,
            ],
        ];

        $repo = $this->em->getRepository(Position::class);
        foreach ($specs as $spec) {
            $departmentId = $departmentIdsByCode[$spec['departmentCode']] ?? null;
            if (!$departmentId) {
                $output->writeln(sprintf('Seed aborted: départementId introuvable pour code=%s.', $spec['departmentCode']));
                return Command::FAILURE;
            }

            $existing = $repo->findOneBy([
                'title' => $spec['title'],
                'department' => $departmentId,
                'level' => $spec['level'],
            ]);

            if ($existing) {
                $existing
                    ->setDescription($spec['description'])
                    ->setHeadcount($spec['headcount'])
                    ->setOpenPositions($spec['openPositions'])
                    ->setStatus($spec['status'])
                ;
                continue;
            }

            $position = new Position();
            $position
                ->setTitle($spec['title'])
                ->setDepartment($departmentId)
                ->setLevel($spec['level'])
                ->setDescription($spec['description'])
                ->setHeadcount($spec['headcount'])
                ->setOpenPositions($spec['openPositions'])
                ->setStatus($spec['status'])
            ;
            $this->em->persist($position);
        }

        $this->em->flush();
        $output->writeln('Positions seeded.');

        return Command::SUCCESS;
    }
}
