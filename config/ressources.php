<?php

declare(strict_types=1);

use App\Entity\User;
use App\Entity\Profile;
use App\Entity\Employee;
use App\Entity\WorkExperience;
use App\Entity\Skill;
use App\Model\Ressource;


return static function (): iterable {

    yield Ressource::new("user", User::class, "US", true);
    yield Ressource::new("profile", Profile::class, "PR", true);
    yield Ressource::new("employee", Employee::class, "EM", true);
    yield Ressource::new("work_experience", WorkExperience::class, "WE", true);
    yield Ressource::new("skill", Skill::class, "SK", true);

};
