<?php

namespace Bex\Behat\StepTimeLoggerExtension\Listener;

use Behat\Behat\EventDispatcher\Event\AfterStepTested;
use Behat\Behat\EventDispatcher\Event\BeforeStepTested;
use Behat\Behat\EventDispatcher\Event\StepTested;
use Behat\Testwork\EventDispatcher\Event\AfterSuiteTested;
use Behat\Testwork\EventDispatcher\Event\SuiteTested;
use Bex\Behat\StepTimeLoggerExtension\ServiceContainer\Config;
use Bex\Behat\StepTimeLoggerExtension\Service\OutputPrinter\OutputPrinterInterface;
use Bex\Behat\StepTimeLoggerExtension\Service\StepTimeLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class StepTimeLoggerListener implements EventSubscriberInterface
{
    /**
     * @var StepTimeLogger
     */
    private $stepTimeLogger;

    /**
     * @param StepTimeLogger         $stepTimeLogger
     * @param OutputPrinterInterface $outputPrinter
     */
    public function __construct(Config $config, StepTimeLogger $stepTimeLogger)
    {
        $this->config = $config;
        $this->stepTimeLogger = $stepTimeLogger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StepTested::BEFORE => 'stepStarted',
            StepTested::AFTER => 'stepFinished',
            SuiteTested::AFTER => 'suiteFinished'
        ];
    }

    /**
     * @param BeforeStepTested $event
     */
    public function stepStarted(BeforeStepTested $event)
    {
        if ($this->config->isEnabled()) {
            $this->stepTimeLogger->logStepStarted($event->getStep()->getText());
        }
    }

    /**
     * @param AfterStepTested $event
     */
    public function stepFinished(AfterStepTested $event)
    {
        if ($this->config->isEnabled()) {
            $this->stepTimeLogger->logStepFinished($event->getStep()->getText());
        }
    }

    /**
     * @param AfterSuiteTested $event
     */
    public function suiteFinished(AfterSuiteTested $event)
    {
        if ($this->config->isEnabled()) {
            $calledCounts = $this->stepTimeLogger->getCalledCounts();
            $avgTimes = $this->stepTimeLogger->getAvegrageExecutionTimes();
            $this->stepTimeLogger->clearLogs();

            foreach ($this->config->getOutputPrinters() as $printer) {
                $printer->printLogs($calledCounts, $avgTimes);
            }
        }
    }
}