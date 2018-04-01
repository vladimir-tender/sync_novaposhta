<?php

namespace App\Messages\Logger;

use Psr\Log\LoggerInterface;

abstract class ALogger
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var array  */
    protected $logStack = [];

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $type
     * @param string $message
     */
    public function addLogToStack(string $type, string $message): void
    {
        if (in_array($type, ['info', 'error', 'notice', 'critical', 'warning', 'alert', 'debug', 'emergency'])) {
            $this->logStack[] = ['type' => $type, 'message' => $message];
        }
    }

    public function unLoadLogStack(): void
    {
        if (!empty($this->logStack)) {
            foreach ($this->logStack as $log) {

                $this->customMessage($log['type'], $log['message']);
            }
            $this->clearLogStack();
        }
    }

    private function clearLogStack(): void
    {
        $this->logStack = [];
    }

    public function customMessage(string $method, string $message): void
    {
        $this->logger->$method($message);
    }
}