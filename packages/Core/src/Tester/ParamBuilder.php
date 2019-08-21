<?php

namespace Beapp\RepositoryTester\Tester;

use ArgumentCountError;
use Beapp\RepositoryTester\Exception\BuildParamException;
use Beapp\RepositoryTester\Exception\NonInstantiableTypeException;
use Beapp\RepositoryTester\Exception\NoTypeFoundException;
use Beapp\RepositoryTester\Exception\UnknownTypeException;
use Exception;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionMethod;

class ParamBuilder
{
    /** @var LoggerInterface */
    private $logger;

    /**
     * ParamBuilder constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param ReflectionMethod $reflectionMethod
     * @return array
     * @throws BuildParamException
     */
    public function getParametersDummyValuesForMethod(ReflectionMethod $reflectionMethod): array
    {
        $reflectionParameters = $reflectionMethod->getParameters();
        $params = [];

        if (empty($reflectionParameters)) {
            $this->logger->debug('Method ' . $reflectionMethod . ' has no parameters');
        }

        foreach ($reflectionParameters as $reflectionParameter) {
            $this->logger->debug('Parameter %paramName% is type of %paramType%', [
                '%paramName%' => $reflectionParameter->getName(),
                '%paramType%' => $reflectionParameter->getType(),
            ]);

            if ($reflectionParameter->isOptional()) {
                continue;
            } elseif (!$reflectionParameter->hasType()) {
                $typeName = $this->extractParamTypeFromDoc($reflectionMethod, $reflectionParameter);

                //Unable to guess type from phpdoc, cancel test.
                if (null === $typeName) {
                    throw new NoTypeFoundException(sprintf('Parameter %s has no type.', $reflectionParameter->getName()));
                }

                $paramValue = $this->getDummyValueForType($typeName);
            } else {
                $reflectionType = $reflectionParameter->getType();

                if ($reflectionType->allowsNull()) {
                    $paramValue = null;
                } else {
                    $paramValue = $this->getDummyValueForType($reflectionType->getName());
                }
            }

            $params[$reflectionParameter->getName()] = $paramValue;
        }

        return $params;
    }

    /**
     * Try to guess param type from phpdoc annotations
     *
     * @param ReflectionMethod $method
     * @param \ReflectionParameter $reflectionParameter
     * @return mixed The type found, or null instead
     */
    public function extractParamTypeFromDoc(ReflectionMethod $method, \ReflectionParameter $reflectionParameter)
    {
        $docLines = explode('*', $method->getDocComment());
        $paramName = '$' . $reflectionParameter->getName();
        $paramAnnotation = '@param';

        $this->logger->debug(json_encode($docLines));

        foreach ($docLines as $docLine) {
            if (!empty($docLine) && false !== strpos($docLine, $paramName) && false !== strpos($docLine, $paramAnnotation)) {
                $exploded = explode(' ', trim($docLine));

                $this->logger->debug(json_encode($exploded));

                $pos = array_search($paramName, $exploded);

                if (false === $pos) {
                    return null;
                }

                $paramType = $exploded[$pos - 1];
                if ($paramType !== '' && $paramType !== $paramAnnotation) {

                    //Annotation can have several types. In this case, we must separate them and choose one.
                    if (false !== strpos($paramType, '|')) {
                        $paramTypes = explode('|', $paramType);
                        $paramType = $paramTypes[0];
                    }

                    $this->logger->info('Type found : ' . $paramType);

                    return $paramType;
                } else {
                    return null;
                }
            }
        }

        return null;
    }

    /**
     * @param string $type
     * @return mixed
     * @throws BuildParamException
     */
    public function getDummyValueForType(string $type)
    {
        switch ($type) {
            case 'string':
                return 'foo';
            case 'int':
            case 'integer':
                return 1;
            case 'float':
                return 1.0;
            case 'bool':
            case 'boolean':
                return true;
            case 'array':
                return [];
        }

        if (class_exists($type)) {
            try {
                $reflectionClass = new ReflectionClass($type);

                $reflectionConstructor = $reflectionClass->getConstructor();
                if ($reflectionConstructor != null) {
                    $constructorParams = $this->getParametersDummyValuesForMethod($reflectionConstructor);
                    return $reflectionClass->newInstance(...array_values($constructorParams));
                }

                return $reflectionClass->newInstance();
            } catch (ArgumentCountError|Exception $e) {
                throw new NonInstantiableTypeException($e->getMessage(), 0, $e);
            }
        }

        throw new UnknownTypeException(sprintf('Unhandled arg type : %s', $type));
    }
}
