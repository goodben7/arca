<?php

namespace App\Service;

use App\Entity\JobRole;
use App\Repository\JobRoleRepository;

class CriticalJobRolesProvider
{
    /** @var list<string>|null */
    private ?array $cachedCodes = null;

    public function __construct(
        private JobRoleRepository $jobRoles,
    ) {
    }

    /**
     * @return list<JobRole>
     */
    public function getCriticalJobRoles(): array
    {
        $roles = [];
        foreach ($this->getCriticalJobRoleCodes() as $code) {
            $role = $this->jobRoles->findOneByCode($code);
            if (null !== $role) {
                $roles[] = $role;
            }
        }

        return $roles;
    }

    /**
     * @return list<string>
     */
    public function getCriticalJobRoleIds(): array
    {
        return array_values(array_filter(array_map(
            static fn (JobRole $role) => $role->getId(),
            $this->getCriticalJobRoles(),
        )));
    }

    /**
     * @return list<string>
     */
    public function getCriticalJobRoleCodes(): array
    {
        if (null === $this->cachedCodes) {
            $loader = require \dirname(__DIR__, 2).'/config/succession.php';
            $this->cachedCodes = iterator_to_array($loader(), false);
        }

        return $this->cachedCodes;
    }
}
