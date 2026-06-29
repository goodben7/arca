<?php

namespace App\Service;

class CompensationConfigProvider
{
    /** @var array{base_salary_per_rank: float} */
    private readonly array $config;

    public function __construct()
    {
        $loader = require \dirname(__DIR__, 2).'/config/compensation.php';
        $this->config = $loader();
    }

    public function getBaseSalaryPerRank(): float
    {
        return (float) $this->config['base_salary_per_rank'];
    }
}
