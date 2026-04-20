<?php

namespace M6Web\Bundle\StatsdPrometheusBundle\Metric;

use M6Web\Bundle\StatsdPrometheusBundle\Client\ClientInterface;
use M6Web\Bundle\StatsdPrometheusBundle\Exception\MetricException;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class MetricHandler
{
    public const METRIC_FORMAT = '%name:%value|%type%tags';

    /** @var ClientInterface */
    protected $client;

    /** @var ContainerInterface */
    protected $container;

    /** @var Request|null */
    protected $request;

    /** @var \SplQueue<MetricInterface> */
    protected $metrics;

    /** @var int */
    protected $maxNumberOfMetricToQueue;

    /** @var bool */
    protected $flushMetricsQueue = false;

    public function __construct()
    {
        $this->initMetricsQueue();
    }

    /**
     * This function tries to send the metrics according to some rules.
     * If those rules are not valid, it won't send anything.
     */
    public function tryToSendMetrics(): bool
    {
        if ($this->hasToSendMetrics()) {
            return $this->sendMetrics();
        }

        return false;
    }

    /**
     * We define here some rules to force sending the metrics even if we are not required to.
     */
    public function hasToSendMetrics(): bool
    {
        return $this->isFlushMetricsQueue() || $this->isMaxNumberOfMetricsReached();
    }

    public function isMaxNumberOfMetricsReached(): bool
    {
        return
            !empty($this->maxNumberOfMetricToQueue)
            && ($this->getMetrics()->count() >= $this->maxNumberOfMetricToQueue);
    }

    public function sendMetrics(): bool
    {
        if ($this->metrics->isEmpty()) {
            return true;
        }

        $this->client->sendLines($this->getMetricsAsArray());
        $this->clearMetricsQueue();

        return true;
    }

    public function addMetricToQueue(MetricInterface $metric): self
    {
        $this->metrics->enqueue($metric);

        return $this;
    }

    public function removeLastMetricFromQueue(): void
    {
        $this->metrics->dequeue();
    }

    public function setClient(ClientInterface $client): void
    {
        $this->client = $client;
    }

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    /**
     * @param \SplQueue<MetricInterface> $queue
     */
    public function setMetricsQueue(\SplQueue $queue): void
    {
        $this->metrics = $queue;
    }

    public function setMaxNumberOfMetricToQueue(int $maxNumberOfMetricToQueue): self
    {
        $this->maxNumberOfMetricToQueue = $maxNumberOfMetricToQueue;

        return $this;
    }

    /**
     * @return \SplQueue<MetricInterface>
     */
    public function getMetrics(): \SplQueue
    {
        return $this->metrics;
    }

    public function isFlushMetricsQueue(): bool
    {
        return $this->flushMetricsQueue;
    }

    public function setFlushMetricsQueue(bool $flushMetricsQueue): self
    {
        $this->flushMetricsQueue = $flushMetricsQueue;

        return $this;
    }

    protected function initMetricsQueue(): void
    {
        $this->metrics = new \SplQueue();
    }

    protected function clearMetricsQueue(): self
    {
        $this->initMetricsQueue();

        return $this;
    }

    /**
     * Format data to send to the server
     *
     * @return array<string>
     */
    protected function getMetricsAsArray(): array
    {
        $metrics = [];
        foreach ($this->getMetrics() as $metric) {
            try {
                $metrics[] = $this->getFormattedMetric($metric);
            } catch (MetricException $e) {
            }
        }

        return $metrics;
    }

    /**
     * DogStatsD FORMAT :
     * <metric>:<value>|<type>|@<sample rate>|#tag:value,another_tag:another_value
     *
     * @throws MetricException
     */
    public function getFormattedMetric(MetricInterface $metric): string
    {
        return $this->getFormattedMetricFromData([
            '%name' => $metric->getResolvedName(),
            '%value' => $metric->getResolvedValue(),
            '%type' => $metric->getResolvedType(),
            '%tags' => $this->formatTagsInline(
                $metric->getResolvedTags([
                    'container' => $this->container,
                    'request' => $this->request,
                ])
            ),
        ]);
    }

    /**
     * @param array<string, string> $data
     */
    protected function getFormattedMetricFromData(array $data): string
    {
        return str_replace(array_keys($data), array_values($data), self::METRIC_FORMAT);
    }

    /**
     * Format metric tags on format "|#tag1:value1,tag2:value2,tag3:value3"
     *
     * @param array<string, string> $tags
     */
    protected function formatTagsInline(array $tags): string
    {
        $formatLines = array_map(
            function ($key, $value) {
                return sprintf('%s:%s', $key, $value);
            },
            array_keys($tags),
            $tags
        );
        $inlineTags = implode(',', $formatLines);

        return !empty($inlineTags) ? '|#'.$inlineTags : '';
    }
}
