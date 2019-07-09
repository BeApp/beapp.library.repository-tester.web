<?php

namespace Beapp\RepositoryTesterBundle\Service;

use Beapp\RepositoryTesterBundle\Exception\BuildParamException;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerInterface;

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
     *
     * @param \ReflectionMethod $method
     * @return array|false
     * @throws BuildParamException
     */
    public function buildParametersForMethod(\ReflectionMethod $method)
    {
        $reflectionParameters = $method->getParameters();
        $params = [];

        if(empty($reflectionParameters)){
            $this->logger->debug('Method '.$method.' has no parameters');
        }

        foreach($reflectionParameters as $reflectionParameter)
        {
            $this->logger->debug('Parameter %paramName% is type of %paramType%', [
                '%paramName%' => $reflectionParameter->getName(),
                'paramType' => $reflectionParameter->getType(),
            ]);

            if($reflectionParameter->isOptional()){
                continue;
            }elseif(!$reflectionParameter->hasType()){
                $typeName = $this->extractParamTypeFromDoc($method, $reflectionParameter);

                //Unable to guess type from phpdoc, cancel test.
                if(null === $typeName){
                    $reason = sprintf('Parameter %s has no type', $reflectionParameter->getName());
                    throw new BuildParamException($reason);
                }

                $paramValue = $this->convertTypeIntoParam($typeName);
            }else{
                $reflectionType = $reflectionParameter->getType();

                if($reflectionType->allowsNull()){
                    $paramValue = null;
                }else{
                    $paramValue = $this->convertTypeIntoParam($reflectionType->getName());
                }
            }

            $params[$reflectionParameter->getName()] = $paramValue;
        }

        return $params;
    }

    /**
     * Try to guess param type from phpdoc annotations
     *
     * @param \ReflectionMethod $method
     * @param \ReflectionParameter $reflectionParameter
     * @return mixed The type found, or null instead
     */
    public function extractParamTypeFromDoc(\ReflectionMethod $method, \ReflectionParameter $reflectionParameter)
    {
        $docLines = explode('*', $method->getDocComment());
        $paramName = '$'.$reflectionParameter->getName();
        $paramAnnotation = '@param';

        $this->logger->debug(json_encode($docLines));

        foreach($docLines as $docLine)
        {
            if(!empty($docLine) && false !== strpos($docLine, $paramName) && false !== strpos($docLine, $paramAnnotation)){
                $exploded = explode(' ', trim($docLine));

                $this->logger->debug(json_encode($exploded));

                $pos = array_search($paramName, $exploded);

                if(false === $pos){
                    return null;
                }

                $paramType = $exploded[$pos - 1];
                if($paramType !== '' && $paramType !== $paramAnnotation){

                    //Annotation can have several types. In this case, we must separate them and choose one.
                    if(false !== strpos($paramType, '|')){
                        $paramTypes = explode('|', $paramType);
                        $paramType = $paramTypes[0];
                    }

                    $this->logger->info('Type found : '.$paramType);

                    return $paramType;
                }else{
                    return null;
                }
            }
        }

        return null;
    }

    /**
     * @param string $type
     * @return array|bool|\DateTime|float|int|string
     * @throws BuildParamException
     */
    public function convertTypeIntoParam(string $type)
    {
        switch($type)
        {
            case 'string':
                return 'foo';
            case 'int':
                return 1;
            case 'bool':
                return true;
            case 'array':
                return [];
            case 'float':
                return 1.0;
            case \DateTime::class:
                return new \DateTime();
            case QueryBuilder::class:
                $reason = 'Unable to mock QueryBuilder argument. Cancel test of this method.';

                throw new BuildParamException($reason);
            default:
                $reason = sprintf('Unhandled arg type : %s', $type);
                throw new BuildParamException($reason);
        }
    }
}
