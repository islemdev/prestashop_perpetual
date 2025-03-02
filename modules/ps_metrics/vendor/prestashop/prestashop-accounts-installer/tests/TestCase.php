<?php

namespace ps_metrics_module_v4_0_10\PrestaShop\PsAccountsInstaller\Tests;

use ps_metrics_module_v4_0_10\Faker\Generator;
class TestCase extends \ps_metrics_module_v4_0_10\PHPUnit\Framework\TestCase
{
    /**
     * @var Generator
     */
    public $faker;
    /**
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
        $this->faker = \ps_metrics_module_v4_0_10\Faker\Factory::create();
    }
}
