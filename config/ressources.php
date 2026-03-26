<?php

declare(strict_types=1);

use App\Entity\Document;
use App\Entity\Contract;
use App\Entity\Application;
use App\Entity\Department;
use App\Entity\Employee;
use App\Entity\JobOffer;
use App\Entity\LeaveRequest;
use App\Entity\Position;
use App\Entity\Profile;
use App\Entity\RecruitmentRequest;
use App\Entity\Skill;
use App\Entity\TrainingRequest;
use App\Entity\TrainingSession;
use App\Entity\TrainingEnrollment;
use App\Entity\User;
use App\Entity\WorkExperience;
use App\Model\Ressource;


return static function (): iterable {

    yield Ressource::new("user", User::class, "US", true);
    yield Ressource::new("profile", Profile::class, "PR", true);
    yield Ressource::new("employee", Employee::class, "EM", true);
    yield Ressource::new("work_experience", WorkExperience::class, "WE", true);
    yield Ressource::new("skill", Skill::class, "SK", true);
    yield Ressource::new("document", Document::class, "DC", true);
    yield Ressource::new("department", Department::class, "DP", true);
    yield Ressource::new("contract", Contract::class, "CT", true);
    yield Ressource::new("leave_request", LeaveRequest::class, "LR", true);
    yield Ressource::new("position", Position::class, "PO", true);
    yield Ressource::new("recruitment_request", RecruitmentRequest::class, "RR", true);
    yield Ressource::new("job_offer", JobOffer::class, "JO", true);
    yield Ressource::new("application", Application::class, "AP", true);
    yield Ressource::new("training_request", TrainingRequest::class, "TR", true);
    yield Ressource::new("training_session", TrainingSession::class, "TS", true);
    yield Ressource::new("training_enrollment", TrainingEnrollment::class, "TE", true);

};
