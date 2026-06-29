<?php

namespace App\Command;

use App\Entity\JobRole;
use App\Entity\JobRoleRequiredSkill;
use App\Entity\Skill;
use App\Entity\SkillCategory;
use App\Model\SkillConstants;
use App\Repository\JobRoleRepository;
use App\Repository\SkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:seed:skills',
    description: 'Seed skills catalog and accounting job role required skills',
)]
class SeedSkillsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private SkillRepository $skills,
        private JobRoleRepository $jobRoles,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (null !== $this->skills->findOneByCode('COMPTA_GEN')) {
            $output->writeln('Seed aborted: le catalogue compétences comptabilité est déjà présent.');

            return Command::FAILURE;
        }

        $jobRoleCodes = ['ACC-JUNIOR', 'ACC', 'ACC-SR', 'ACC-LEAD', 'CFO'];
        $roles = [];

        foreach ($jobRoleCodes as $code) {
            $role = $this->jobRoles->findOneByCode($code);
            if (null === $role) {
                $output->writeln(sprintf(
                    'Seed aborted: fiche métier %s introuvable. Lance app:seed:job-architecture avant.',
                    $code,
                ));

                return Command::FAILURE;
            }
            $roles[$code] = $role;
        }

        $category = $this->createAccountingCategory();
        $this->em->flush();

        $catalogSkills = $this->createAccountingSkills($category);
        $this->em->flush();

        $requiredCount = $this->createAccountingRequiredSkills($roles, $catalogSkills);
        $this->em->flush();

        $output->writeln(sprintf(
            'Skills seeded: 1 category, %d skills, %d job role required skills.',
            count($catalogSkills),
            $requiredCount,
        ));

        return Command::SUCCESS;
    }

    private function createAccountingCategory(): SkillCategory
    {
        $category = (new SkillCategory())
            ->setCode('COMPTA')
            ->setName('Comptabilité & Finance')
            ->setDescription('Compétences métier de la filière comptable et financière');

        $this->em->persist($category);

        return $category;
    }

    /**
     * @return array<string, Skill>
     */
    private function createAccountingSkills(SkillCategory $category): array
    {
        $specs = [
            'COMPTA_GEN' => [
                'name' => 'Comptabilité générale',
                'description' => 'Tenue des comptes, écritures, clôtures et principes comptables',
            ],
            'EXCEL' => [
                'name' => 'Excel avancé',
                'description' => 'Tableaux croisés dynamiques, formules avancées et modèles financiers',
            ],
            'FISCALITE' => [
                'name' => 'Fiscalité',
                'description' => 'TVA, déclarations fiscales et veille réglementaire',
            ],
            'SAP_FI' => [
                'name' => 'SAP FI',
                'description' => 'Module finance SAP : comptabilité, immobilisations et reporting',
            ],
            'AUDIT' => [
                'name' => 'Audit interne',
                'description' => 'Contrôles internes, procédures et préparation aux audits',
            ],
            'TRESORERIE' => [
                'name' => 'Trésorerie',
                'description' => 'Gestion de trésorerie, prévisions de cash et relations bancaires',
            ],
            'REPORTING' => [
                'name' => 'Reporting financier',
                'description' => 'Consolidation, tableaux de bord et reporting direction',
            ],
            'MANAGEMENT_EQ' => [
                'name' => 'Management d\'équipe',
                'description' => 'Encadrement, délégation et pilotage d\'une équipe comptable',
            ],
        ];

        $skills = [];
        foreach ($specs as $code => $spec) {
            $skill = (new Skill())
                ->setCode($code)
                ->setName($spec['name'])
                ->setCategory($category)
                ->setDescription($spec['description']);
            $this->em->persist($skill);
            $skills[$code] = $skill;
        }

        return $skills;
    }

    /**
     * @param array<string, JobRole> $roles
     * @param array<string, Skill>    $skills
     */
    private function createAccountingRequiredSkills(array $roles, array $skills): int
    {
        $requirements = [
            'ACC-JUNIOR' => [
                ['skill' => 'COMPTA_GEN', 'level' => SkillConstants::LEVEL_BEGINNER],
                ['skill' => 'EXCEL', 'level' => SkillConstants::LEVEL_BEGINNER],
            ],
            'ACC' => [
                ['skill' => 'COMPTA_GEN', 'level' => SkillConstants::LEVEL_INTERMEDIATE],
                ['skill' => 'EXCEL', 'level' => SkillConstants::LEVEL_INTERMEDIATE],
                ['skill' => 'FISCALITE', 'level' => SkillConstants::LEVEL_BEGINNER],
            ],
            'ACC-SR' => [
                ['skill' => 'COMPTA_GEN', 'level' => SkillConstants::LEVEL_ADVANCED],
                ['skill' => 'SAP_FI', 'level' => SkillConstants::LEVEL_INTERMEDIATE],
                ['skill' => 'AUDIT', 'level' => SkillConstants::LEVEL_INTERMEDIATE],
                ['skill' => 'REPORTING', 'level' => SkillConstants::LEVEL_INTERMEDIATE],
            ],
            'ACC-LEAD' => [
                ['skill' => 'SAP_FI', 'level' => SkillConstants::LEVEL_ADVANCED],
                ['skill' => 'AUDIT', 'level' => SkillConstants::LEVEL_ADVANCED],
                ['skill' => 'REPORTING', 'level' => SkillConstants::LEVEL_ADVANCED],
                ['skill' => 'MANAGEMENT_EQ', 'level' => SkillConstants::LEVEL_INTERMEDIATE],
            ],
            'CFO' => [
                ['skill' => 'REPORTING', 'level' => SkillConstants::LEVEL_EXPERT],
                ['skill' => 'TRESORERIE', 'level' => SkillConstants::LEVEL_EXPERT],
                ['skill' => 'MANAGEMENT_EQ', 'level' => SkillConstants::LEVEL_EXPERT],
                ['skill' => 'FISCALITE', 'level' => SkillConstants::LEVEL_ADVANCED],
            ],
        ];

        $count = 0;
        foreach ($requirements as $roleCode => $roleRequirements) {
            foreach ($roleRequirements as $requirement) {
                $requiredSkill = (new JobRoleRequiredSkill())
                    ->setJobRole($roles[$roleCode])
                    ->setSkill($skills[$requirement['skill']])
                    ->setMinimumLevel($requirement['level']);
                $this->em->persist($requiredSkill);
                ++$count;
            }
        }

        return $count;
    }
}
