<?php

namespace Beapp\Tester\Repository\Test;

use Beapp\Tester\Repository\RepositoryTester;
use Doctrine\Common\Persistence\ObjectRepository;
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
        return $this->getRepositoryTester()->getMethodsForUnitTest();
    }

    /**
     * @test
     * @dataProvider getRepositoriesMethods
     *
     * @param \ReflectionMethod $method
     * @param ObjectRepository $repositoryInstance
     * @param array $params
     */
    public function testRepositories(\ReflectionMethod $method, ObjectRepository $repositoryInstance, array $params)
    {
        $result = $this->getRepositoryTester()->unitaryTestMethod($method, $repositoryInstance, $params);

        $this->assertEquals(true, $result['success'], $result['reason']);
    }
}
