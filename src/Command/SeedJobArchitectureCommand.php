<?php

namespace App\Command;

use App\Entity\CareerPath;
use App\Entity\Grade;
use App\Entity\JobFamily;
use App\Entity\JobRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:seed:job-architecture',
    description: 'Seed job architecture referential (families, grades, accounting roles, career paths)',
)]
class SeedJobArchitectureCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $jobFamilyRepo = $this->em->getRepository(JobFamily::class);

        if ($jobFamilyRepo->count([]) > 0) {
            $output->writeln('Seed aborted: le référentiel emplois n’est pas vide.');

            return Command::FAILURE;
        }

        $families = $this->createJobFamilies();
        $grades = $this->createGrades();
        $this->em->flush();

        $roles = $this->createAccountingJobRoles($families['FIN'], $grades);
        $this->em->flush();

        $this->createAccountingCareerPaths($roles);
        $this->em->flush();

        $output->writeln(sprintf(
            'Job architecture seeded: %d families, %d grades, %d roles, %d career paths.',
            count($families),
            count($grades),
            count($roles),
            count($roles) - 1,
        ));

        return Command::SUCCESS;
    }

    /**
     * @return array<string, JobFamily>
     */
    private function createJobFamilies(): array
    {
        $specs = [
            'FIN' => ['name' => 'Finance', 'description' => 'Comptabilité, contrôle de gestion et trésorerie'],
            'IT' => ['name' => 'Informatique', 'description' => 'Développement, infrastructure et support SI'],
            'RH' => ['name' => 'Ressources Humaines', 'description' => 'Administration du personnel, paie et développement RH'],
        ];

        $families = [];
        foreach ($specs as $code => $spec) {
            $family = (new JobFamily())
                ->setCode($code)
                ->setName($spec['name'])
                ->setDescription($spec['description']);
            $this->em->persist($family);
            $families[$code] = $family;
        }

        return $families;
    }

    /**
     * @return array<string, Grade>
     */
    private function createGrades(): array
    {
        $specs = [
            'G1' => ['name' => 'Grade 1 — Junior', 'rank' => 1],
            'G2' => ['name' => 'Grade 2', 'rank' => 2],
            'G3' => ['name' => 'Grade 3', 'rank' => 3],
            'G4' => ['name' => 'Grade 4 — Manager', 'rank' => 4],
            'G5' => ['name' => 'Grade 5 — Direction', 'rank' => 5],
        ];

        $grades = [];
        foreach ($specs as $code => $spec) {
            $grade = (new Grade())
                ->setCode($code)
                ->setName($spec['name'])
                ->setRank($spec['rank']);
            $this->em->persist($grade);
            $grades[$code] = $grade;
        }

        return $grades;
    }

    /**
     * @param array<string, Grade> $grades
     *
     * @return array<string, JobRole>
     */
    private function createAccountingJobRoles(JobFamily $financeFamily, array $grades): array
    {
        $specs = [
            'ACC-JUNIOR' => [
                'title' => 'Comptable junior',
                'grade' => 'G1',
                'description' => 'Saisie comptable, rapprochements bancaires et pièces justificatives',
            ],
            'ACC' => [
                'title' => 'Comptable',
                'grade' => 'G2',
                'description' => 'Tenue des comptes, clôtures mensuelles et déclarations fiscales courantes',
            ],
            'ACC-SR' => [
                'title' => 'Comptable senior',
                'grade' => 'G3',
                'description' => 'Supervision des clôtures, contrôles internes et reporting financier',
            ],
            'ACC-LEAD' => [
                'title' => 'Chef comptable',
                'grade' => 'G4',
                'description' => 'Management de l’équipe comptable et coordination des audits',
            ],
            'CFO' => [
                'title' => 'Directeur financier',
                'grade' => 'G5',
                'description' => 'Pilotage financier, stratégie et relation avec la direction générale',
            ],
        ];

        $roles = [];
        foreach ($specs as $code => $spec) {
            $role = (new JobRole())
                ->setCode($code)
                ->setTitle($spec['title'])
                ->setJobFamily($financeFamily)
                ->setGrade($grades[$spec['grade']])
                ->setDescription($spec['description']);
            $this->em->persist($role);
            $roles[$code] = $role;
        }

        return $roles;
    }

    /**
     * @param array<string, JobRole> $roles
     */
    private function createAccountingCareerPaths(array $roles): void
    {
        $transitions = [
            ['from' => 'ACC-JUNIOR', 'to' => 'ACC', 'minTenureMonths' => 12],
            ['from' => 'ACC', 'to' => 'ACC-SR', 'minTenureMonths' => 24],
            ['from' => 'ACC-SR', 'to' => 'ACC-LEAD', 'minTenureMonths' => 36],
            ['from' => 'ACC-LEAD', 'to' => 'CFO', 'minTenureMonths' => 48],
        ];

        foreach ($transitions as $transition) {
            $path = (new CareerPath())
                ->setFromJobRole($roles[$transition['from']])
                ->setToJobRole($roles[$transition['to']])
                ->setConditions(['minTenureMonths' => $transition['minTenureMonths']]);
            $this->em->persist($path);
        }
    }
}
