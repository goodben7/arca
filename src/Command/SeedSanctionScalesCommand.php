<?php

namespace App\Command;

use App\Entity\SanctionScale;
use App\Model\SanctionScaleConstants;
use App\Repository\SanctionScaleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:seed:sanction-scales',
    description: 'Seed the default disciplinary sanction scale (WARN → DISMISS)',
)]
class SeedSanctionScalesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private SanctionScaleRepository $sanctionScales,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (null !== $this->sanctionScales->findOneByCode(SanctionScaleConstants::CODE_WARN)) {
            $output->writeln('Seed aborted: l\'échelle des sanctions est déjà présente.');

            return Command::FAILURE;
        }

        $specs = [
            [
                'code' => SanctionScaleConstants::CODE_WARN,
                'label' => 'Avertissement',
                'severityLevel' => 1,
                'requiresHearing' => false,
                'maxDurationDays' => null,
            ],
            [
                'code' => SanctionScaleConstants::CODE_BLAME,
                'label' => 'Blâme',
                'severityLevel' => 2,
                'requiresHearing' => true,
                'maxDurationDays' => null,
            ],
            [
                'code' => SanctionScaleConstants::CODE_SUSPEND,
                'label' => 'Mise à pied',
                'severityLevel' => 3,
                'requiresHearing' => true,
                'maxDurationDays' => 8,
            ],
            [
                'code' => SanctionScaleConstants::CODE_DISMISS,
                'label' => 'Licenciement',
                'severityLevel' => 4,
                'requiresHearing' => true,
                'maxDurationDays' => null,
            ],
        ];

        foreach ($specs as $spec) {
            $scale = (new SanctionScale())
                ->setCode($spec['code'])
                ->setLabel($spec['label'])
                ->setSeverityLevel($spec['severityLevel'])
                ->setRequiresHearing($spec['requiresHearing'])
                ->setMaxDurationDays($spec['maxDurationDays'])
                ->setActive(true);

            $this->em->persist($scale);
        }

        $this->em->flush();

        $output->writeln(sprintf('Sanction scales seeded: %d levels.', count($specs)));

        return Command::SUCCESS;
    }
}
