<?php

namespace M6Web\Bundle\StatsdPrometheusBundle\Event\Console;

use M6Web\Bundle\StatsdPrometheusBundle\Event\AbstractMonitoringEvent;

class ConsoleTerminateMonitoringEvent extends AbstractMonitoringEvent
{
    public static function fromFacade(ConsoleMonitoringEventFacade $facade): self
    {
        return new self($facade->toMonitoringArray());
    }
}
