<?php

namespace M6Web\Bundle\StatsdPrometheusBundle\Tests\DependencyInjection;

use M6Web\Bundle\StatsdPrometheusBundle\DependencyInjection\M6WebStatsdPrometheusExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

final class M6WebStatsdPrometheusExtensionTest extends TestCase
{
    private ContainerBuilder $container;

    private M6WebStatsdPrometheusExtension $extension;

    /**
     * @param array<mixed> $config
     * @param array<mixed> $expected
     */
    #[DataProvider('dataProviderForGetServersReturnsExpectation')]
    public function testGetServersReturnsExpectation(array $config, array $expected): void
    {
        // -- When --
        $this->extension->load([$config], $this->container);
        // -- Then --
        $this->assertEquals($expected, $this->extension->getServers());
    }

    /**
     * @param array<mixed> $config
     * @param array<mixed> $expected
     */
    #[DataProvider('dataProviderForGetClientsReturnsExpectation')]
    public function testGetClientsReturnsExpectation(array $config, array $expected): void
    {
        // -- When --
        $this->extension->load([$config], $this->container);
        // -- Then --
        $this->assertEquals($expected, $this->extension->getClients());
    }

    #[DoesNotPerformAssertions]
    public function testLoadCorrectTagsConfigurationDoesNoesNotThrowException(): void
    {
        // -- Given --
        $config = [
            'tags' => [
                'tagA' => 'tagAValue',
                'tagB' => 'tagAValueB',
            ],
        ];
        // -- Expects --
        // NO exceptions
        // -- When --
        $this->extension->load([$config], $this->container);
    }

    public function testLoadWrongTagsConfigurationDoesThrowsException(): void
    {
        // -- Given --
        $config = [
            'tagged' => [
                ['tagB'],
            ],
        ];
        // -- Expects --
        $this->expectException(InvalidConfigurationException::class);
        // -- When --
        $this->extension->load([$config], $this->container);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testLoadCorrectYmlConfigurationFileDoesNotThrowException(): void
    {
        // -- Given --
        $config = Yaml::parseFile(__DIR__.'/../Fixtures/CorrectConfigurationFileTest.yml');
        // -- Expects --
        // NO exceptions
        // -- When --
        $this->extension->load([$config[M6WebStatsdPrometheusExtension::CONFIG_ROOT_KEY]], $this->container);
    }

    public function testLoadWrongYmlConfigurationFileThrowsException(): void
    {
        // -- Given --
        $config = Yaml::parseFile(__DIR__.'/../Fixtures/WrongConfigurationFileTest.yml');
        // -- Expects --
        $this->expectException(InvalidConfigurationException::class);
        // -- When --
        $this->extension->load([$config[M6WebStatsdPrometheusExtension::CONFIG_ROOT_KEY]], $this->container);
    }

    /**
     * @return array<mixed>
     */
    public static function dataProviderForGetServersReturnsExpectation(): array
    {
        return [
            'test1' => [
                // Configuration
                [
                    'servers' => [
                        'default' => [
                            'address' => 'udp://192.168.1.1',
                            'port' => 3000,
                        ],
                    ],
                ],
                // Expected result
                [
                    'default' => [
                        'address' => 'udp://192.168.1.1',
                        'port' => 3000,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public static function dataProviderForGetClientsReturnsExpectation(): array
    {
        return [
            'test1' => [
                // Configuration (long one)
                [
                    'servers' => [
                        'default' => [
                            'address' => 'udp://address',
                            'port' => '3321',
                        ],
                    ],
                    'clients' => [
                        'default' => [
                            'server' => 'default',
                            'max_queued_metrics' => 100,
                            'groups' => [
                                'groupA' => [
                                    'tags' => [
                                        'tagB' => 'test',
                                    ],
                                    'events' => [
                                        'eventNameA' => [
                                            'flush_metrics_queue' => true,
                                            'metrics' => [
                                                [
                                                    'type' => 'increment',
                                                    'name' => 'metricName',
                                                    'tags' => ['tagA', 'tagB'],
                                                ],
                                                [
                                                    'type' => 'timer',
                                                    'name' => 'metricName',
                                                    'tags' => ['tagC', 'tagD'],
                                                    'param_value' => 'paramValue',
                                                ],
                                            ],
                                        ],
                                        'eventNameB' => [
                                            'metrics' => [
                                                [
                                                    'type' => 'gauge',
                                                    'name' => 'metricName',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                'groupB' => [
                                    'tags' => [
                                        'tagC' => 'projectName',
                                    ],
                                    'events' => [
                                        'eventNameC' => [
                                            'metrics' => [
                                                [
                                                    'type' => 'counter',
                                                    'name' => 'metricName',
                                                    'tags' => ['tagC', 'tagD'],
                                                ],
                                            ],
                                        ],
                                        'eventNameD' => [
                                            'flush_metrics_queue' => true,
                                            'metrics' => [
                                                [
                                                    'type' => 'timer',
                                                    'name' => 'metricName',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                // Expected Clients
                [
                    'default' => [
                        'server' => 'default',
                        'max_queued_metrics' => 100,
                        'groups' => [
                            'groupA' => [
                                'tags' => [
                                    'tagB' => 'test',
                                ],
                                'events' => [
                                    'eventNameA' => [
                                        'flush_metrics_queue' => true,
                                        'metrics' => [
                                            [
                                                'type' => 'increment',
                                                'name' => 'metricName',
                                                'tags' => ['tagA', 'tagB'],
                                            ],
                                            [
                                                'type' => 'timer',
                                                'name' => 'metricName',
                                                'tags' => ['tagC', 'tagD'],
                                                'param_value' => 'paramValue',
                                            ],
                                        ],
                                    ],
                                    'eventNameB' => [
                                        'flush_metrics_queue' => false,
                                        'metrics' => [
                                            [
                                                'type' => 'gauge',
                                                'name' => 'metricName',
                                                'tags' => [],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'groupB' => [
                                'tags' => [
                                    'tagC' => 'projectName',
                                ],
                                'events' => [
                                    'eventNameC' => [
                                        'flush_metrics_queue' => false,
                                        'metrics' => [
                                            [
                                                'type' => 'counter',
                                                'name' => 'metricName',
                                                'tags' => ['tagC', 'tagD'],
                                            ],
                                        ],
                                    ],
                                    'eventNameD' => [
                                        'flush_metrics_queue' => true,
                                        'metrics' => [
                                            [
                                                'type' => 'timer',
                                                'name' => 'metricName',
                                                'tags' => [],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new ContainerBuilder();
        $this->extension = new M6WebStatsdPrometheusExtension();
    }
}
