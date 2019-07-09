<?php

namespace Beapp\RepositoryTesterBundle\Service;

use Beapp\RepositoryTesterBundle\Service\Repository\RepositoryTester;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ClassParserTest extends TestCase
{
    /**
     * @return array
     */
    public function classProvider(): array
    {
        return [
            [RepositoryTester::class]
        ];
    }

    /**
     * @test
     * @dataProvider classProvider
     *
     * @param string $className
     * @throws \ReflectionException
     */
    public function testCrawlMethodsFrom(string $className)
    {
        $result = $this->getClassParser()->crawlMethodsFrom($className);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertInstanceOf(\ReflectionMethod::class, $result[0]);
    }

    /**
     * @return ClassParser
     */
    public function getClassParser(): ClassParser
    {
        $logger = $this->createMock(LoggerInterface::class);

        $logger->expects($this->any())
            ->method('debug');
        $logger->expects($this->any())
            ->method('notice');

        return new ClassParser($logger);
    }
}
