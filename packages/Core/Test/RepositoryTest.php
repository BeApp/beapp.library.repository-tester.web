<?php

namespace Beapp\Tester\Repository\Test;

use Beapp\RepositoryTesterBundle\Service\Repository\RepositoryTester;
use Beapp\RepositoryTesterBundle\Test\MethodTester;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Kernel;

/**
 * This class extends WebTestCase in order to access repositories from the container.
 * We can't use KernelTestCase class to do this because we can't access container
 * from dataProvider functions with this one.
 *
 * Class RepositoryTest
 * @package Beapp\Tester\Repository
 */
class RepositoryTest extends WebTestCase
{
    /**
     * @return RepositoryTester
     */
    public function getRepositoryTester(): RepositoryTester
    {
        if(Kernel::MAJOR_VERSION === 3){
            $client = self::createClient();
            $container = $client->getContainer();
        }elseif(Kernel::MAJOR_VERSION === 4){
            $container = self::$container;
        }else{
            throw new \LogicException('Invalid Symfony version used');
        }

        return $container->get('beapp.doctrine.repository_tester');
    }

    /**
     * @return array
     */
    public function getRepositoriesMethods(): array
    {
        return $this->getRepositoryTester()->crawlRepositories(true);
    }

    /**
     * @test
     * @dataProvider getRepositoriesMethods
     *
     * @param MethodTester $methodTester
     */
    public function testRepositories(MethodTester $methodTester)
    {
        $result = $this->getRepositoryTester()->testMethod($methodTester);

        $this->assertEquals(true, $result['success'], $result['reason']);
    }
}
