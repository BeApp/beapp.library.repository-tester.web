<?php

namespace Beapp\RepositoryTesterBundle\Test;

use Beapp\RepositoryTester\Report\PHPUnitReporter;
use Beapp\RepositoryTester\Report\TestReporter;
use Beapp\RepositoryTester\RepositoryTester;
use Beapp\RepositoryTester\Tester\MethodTester;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * This class extends WebTestCase in order to access repositories from the container.
 * We can't use KernelTestCase class to do this because we can't access container
 * from dataProvider functions with this one.
 */
class RepositoryTest extends WebTestCase
{
    /** @var TestReporter */
    private $phpUnitReporter;

    /**
     * @return RepositoryTester
     */
    private function getRepositoryTester(): RepositoryTester
    {
        $client = self::createClient();
        $container = $client->getContainer();

        return $container->get('beapp.service.repository_tester');
    }

    protected function setUp(): void
    {
        $this->phpUnitReporter = new PHPUnitReporter();
    }

    /**
     * @return MethodTester[]
     * @throws \ReflectionException
     */
    public function getRepositoriesMethods(): array
    {
        $methodTesters = $this->getRepositoryTester()->crawlMethodTesters();

        $dataSet = [];
        foreach ($methodTesters as $methodTester) {
            $dataSet[$methodTester->getTestedClass() . '.' . $methodTester->getMethod()->getName()] = [$methodTester];
        }

        return $dataSet;
    }

    /**
     * @dataProvider getRepositoriesMethods
     *
     * @param MethodTester $methodTester
     */
    public function testRepositories(MethodTester $methodTester)
    {
        $this->getRepositoryTester()->testMethod($this->phpUnitReporter, $methodTester);
    }
}
