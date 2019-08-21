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
    private $PHPUnitReporter;

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
        $this->PHPUnitReporter = new PHPUnitReporter();
    }

    /**
     * @return MethodTester[]
     * @throws \ReflectionException
     */
    public function getRepositoriesMethods(): array
    {
        return $this->getRepositoryTester()->crawlMethodTesters();
    }

    /**
     * @dataProvider getRepositoriesMethods
     *
     * @param MethodTester $methodTester
     */
    public function testRepositories(MethodTester $methodTester)
    {
        $result = $this->getRepositoryTester()->testMethod($this->PHPUnitReporter, $methodTester);

        $this->assertEquals(true, $result['success'], $result['reason']);
    }
}
