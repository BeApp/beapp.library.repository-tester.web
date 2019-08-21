<?php

namespace Beapp\RepositoryTester\Internal\Logger;

use Psr\Log\AbstractLogger;

class ConsoleLogger extends AbstractLogger
{

    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = array())
    {
        if (!empty($context)) {
            foreach ($context as $key => $value) {
                $message = str_replace($key, $value, $message);
                unset($context[$key]);
            }
        }

        echo strtoupper($level);
        echo ": ";
        echo $message;

        if (!empty($context)) {
            echo " [";
            echo join(',', $context);
            echo "]";
        }

        echo "\n";
    }
}