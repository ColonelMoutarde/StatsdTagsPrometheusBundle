<?php

namespace M6Web\Bundle\StatsdPrometheusBundle\Metric;

use M6Web\Bundle\StatsdPrometheusBundle\Exception\MetricException;

interface MetricInterface
{
    /**
     * @throws MetricException
     */
    public function getResolvedName(): string;

    /**
     * @throws MetricException
     */
    public function getResolvedValue(): string;

    /**
     * @throws MetricException
     */
    public function getResolvedType(): string;

    /**
     * @param array<string, mixed> $resolvers an associative array of resolvers
     *                                        ['resolver1' => $resolver1]
     *                                        Used to inject services in tag names:
     *                                        format: '@=my_service.myFunction()'
     *
     * @return array<string, string>
     */
    public function getResolvedTags(array $resolvers): array;
}
