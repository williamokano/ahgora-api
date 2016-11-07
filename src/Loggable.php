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

    /**
     * If some logger is defined, log the info with the given severity level.
     *
     * @param string $level
     * @param string $message
     * @param array  $context
     *
     * @return $this
     */
    protected function log($level, $message, $context = [])
    {
        if ($this->logger !== null) {
            $this->logger->log($level, $message, $context);
        }

        return $this;
    }

    /**
     * Log a message, and maybe the context, with the severity level of EMERGENCY.
     *
     * @param string $message
     * @param array  $context
     *
     * @return $this
     */
    protected function emergency($message, $context = [])
    {
        return $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Log a message, and maybe the context, with the severity level of ALERT.
     *
     * @param string $message
     * @param array  $context
     *
     * @return $this
     */
    protected function alert($message, $context = [])
    {
        return $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * Log a message, and maybe the context, with the severity level of CRITICAL.
     *
     * @param string $message
     * @param array  $context
     *
     * @return $this
     */
    protected function critical($message, $context = [])
    {
        return $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Log a message, and maybe the context, with the severity level of ERROR.
     *
     * @param string $message
     * @param array  $context
     *
     * @return $this
     */
    protected function error($message, $context = [])
    {
        return $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * Log a message, and maybe the context, with the severity level of WARNING.
     *
     * @param string $message
     * @param array  $context
     *
     * @return $this
     */
    protected function warning($message, $context = [])
    {
        return $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * Log a message, and maybe the context, with the severity level of NOTICE.
     *
     * @param string $message
     * @param array  $context
     *
     * @return $this
     */
    protected function notice($message, $context = [])
    {
        return $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Log a message, and maybe the context, with the severity level of INFO.
     *
     * @param string $message
     * @param array  $context
     *
     * @return $this
     */
    protected function info($message, $context = [])
    {
        return $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * Log a message, and maybe the context, with the severity level of DEBUG.
     *
     * @param string $message
     * @param array  $context
     *
     * @return $this
     */
    protected function debug($message, $context = [])
    {
        return $this->log(LogLevel::DEBUG, $message, $context);
    }
}
