<?php

namespace Katapoka\Ahgora;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

trait Loggable
{
    /** @var LoggerInterface */
    private $logger;

    /**
     * Set a logger to the API. Must implement de PHP FIG PSR-3 LoggerInterface.
     *
     * @param LoggerInterface $logger
     *
     * @return $this return the instance for method chaining
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    protected function log($level, $message, $context = [])
    {
        if ($this->logger !== null) {
            $this->logger->log($level, $message, $context);
        }

        return $this;
    }

    protected function emergency($message, $context = [])
    {
        return $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    protected function alert($message, $context = [])
    {
        return $this->log(LogLevel::ALERT, $message, $context);
    }

    protected function critical($message, $context = [])
    {
        return $this->log(LogLevel::CRITICAL, $message, $context);
    }

    protected function error($message, $context = [])
    {
        return $this->log(LogLevel::ERROR, $message, $context);
    }

    protected function warning($message, $context = [])
    {
        return $this->log(LogLevel::WARNING, $message, $context);
    }

    protected function notice($messsage, $context = [])
    {
        return $this->log(LogLevel::NOTICE, $messsage, $context);
    }

    protected function info($message, $context = [])
    {
        return $this->log(LogLevel::INFO, $message, $context);
    }

    protected function debug($message, $context = [])
    {
        return $this->log(LogLevel::DEBUG, $message, $context);
    }
}
