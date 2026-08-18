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
    description: 'Seed or update the default disciplinary sanction scale (REPRIMAND → DISMISS)',
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
        $created = 0;
        $updated = 0;

        foreach ($this->specs() as $spec) {
            $scale = $this->sanctionScales->findOneByCode($spec['code']);

            if (null === $scale) {
                $scale = (new SanctionScale())->setCode($spec['code']);
                $this->em->persist($scale);
                ++$created;
            } else {
                ++$updated;
            }

            $scale
                ->setLabel($spec['label'])
                ->setSeverityLevel($spec['severityLevel'])
                ->setRequiresHearing($spec['requiresHearing'])
                ->setMaxDurationDays($spec['maxDurationDays'])
                ->setActive(true);
        }

        $this->em->flush();

        $output->writeln(sprintf(
            'Sanction scales synced: %d created, %d updated.',
            $created,
            $updated,
        ));

        return Command::SUCCESS;
    }

    /**
     * @return list<array{code: string, label: string, severityLevel: int, requiresHearing: bool, maxDurationDays: ?int}>
     */
    private function specs(): array
    {
        return [
            [
                'code' => SanctionScaleConstants::CODE_REPRIMAND,
                'label' => 'Réprimande / Observation',
                'severityLevel' => 1,
                'requiresHearing' => false,
                'maxDurationDays' => null,
            ],
            [
                'code' => SanctionScaleConstants::CODE_WARN,
                'label' => 'Avertissement',
                'severityLevel' => 2,
                'requiresHearing' => false,
                'maxDurationDays' => null,
            ],
            [
                'code' => SanctionScaleConstants::CODE_BLAME,
                'label' => 'Blâme',
                'severityLevel' => 3,
                'requiresHearing' => true,
                'maxDurationDays' => null,
            ],
            [
                'code' => SanctionScaleConstants::CODE_SUSPEND,
                'label' => 'Mise à pied disciplinaire',
                'severityLevel' => 4,
                'requiresHearing' => true,
                'maxDurationDays' => 8,
            ],
            [
                'code' => SanctionScaleConstants::CODE_DISMISS,
                'label' => 'Licenciement pour faute',
                'severityLevel' => 5,
                'requiresHearing' => true,
                'maxDurationDays' => null,
            ],
        ];
    }
}
