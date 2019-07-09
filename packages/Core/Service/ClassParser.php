<?php

namespace Beapp\RepositoryTesterBundle\Service;

use Psr\Log\LoggerInterface;

class ClassParser
{
    /** @var LoggerInterface */
    private $logger;

    /**
     * ClassParser constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $className
     * @return \ReflectionMethod[]
     * @throws \ReflectionException
     */
    public function crawlMethodsFrom(string $className): array
    {
        $methods = get_class_methods($className);
        $reflectionMethods = [];

        $this->logger->notice('Crawl methods of class : '.$className);

        foreach($methods as $methodName){
            $this->logger->debug('Method name : '.$methodName);
            $reflectionMethod = new \ReflectionMethod($className, $methodName);

            if($reflectionMethod->class !== $className){
                $this->logger->debug('Method '.$methodName.' is herited from another class, skip it.');
                continue;
            }elseif(!$reflectionMethod->isPublic()){
                $this->logger->debug('Method '.$methodName.' isn\'t public, skip it.');
                continue;
            }

            $reflectionMethods[] = $reflectionMethod;
        }

        return $reflectionMethods;
    }
}
