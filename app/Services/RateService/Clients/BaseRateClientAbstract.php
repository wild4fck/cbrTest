<?php

namespace App\Services\RateService\Clients;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use App\Services\RateService\Clients\Interfaces\RateClientInterface;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
abstract class BaseRateClientAbstract implements RateClientInterface
{
    /**
     * @var \GuzzleHttp\Client
     */
    public Client $client;
    
    /**
     * Параметры клиента
     * @var array
     */
    protected array $params = [
        'timeout' => 2.0
    ];
    
    public function __construct() {
        $this->initClient();
    }
    
    /**
     * @return void
     */
    protected function initClient(): void {
        $this->client = new Client([
            'base_uri' => $this->getBaseUrl(),
            'timeout' => $this->getParams()['timeout'],
        ]);
    }
    
    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }
    
    /**
     * @param array $params
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }
    
    /**
     * @param string     $url
     * @param null|array $params
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getRequest(string $url, array $params = null): ResponseInterface
    {
        $options = $params ? ['query' => $params] : [];
        return $this->client->request('GET', $url, $options);
    }
    
    /**
     * @return string
     */
    abstract protected function getBaseUrl(): string;
}
