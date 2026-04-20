<?php

namespace M6Web\Bundle\StatsdPrometheusBundle\Client;

use M6Web\Bundle\StatsdPrometheusBundle\Exception\ServerException;

class Server implements ServerInterface
{
    /** @phpstan-ignore property.onlyWritten */
    private string $name;

    /** @var mixed string format udp://.+ */
    private $address;

    /** @var mixed int port number */
    private $port;

    /**
     * Server constructor.
     *
     * @param array<string, mixed> $serverConfig
     *
     * @throws ServerException
     */
    public function __construct(string $serverName, array $serverConfig)
    {
        if ($this->checkServersConfigurations($serverName, $serverConfig)) {
            $this->name = $serverName;
            $this->address = $serverConfig['address'];
            $this->port = (int) $serverConfig['port'];
        }
    }

    /**
     * Init the servers defined in the app configuration
     *
     * @param array<string, mixed> $serverConfig
     *
     * @throws ServerException
     */
    protected function checkServersConfigurations(string $serverName, array $serverConfig): bool
    {
        if (empty($serverConfig)) {
            throw new ServerException('No servers have been configured');
        }

        if (!isset($serverConfig['address'], $serverConfig['port'])) {
            throw new ServerException($serverName.' : no address or port in the configuration');
        }
        if (!str_starts_with($serverConfig['address'], 'udp://')) {
            throw new ServerException($serverName.' : address should begin with udp://');
        }

        return true;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getPort(): int
    {
        return $this->port;
    }
}
