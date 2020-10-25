<?php


namespace Yetione\Gateway\Services;


use Yetione\Gateway\Exceptions\InvalidServiceException;
use Yetione\Gateway\Options\HttpClientOptions;
use Yetione\Gateway\Routing\Contracts\ServiceContract;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ServiceRegistry
{
    /**
     * @var ServiceContract[]
     */
    protected array $items = [];

    protected ServiceFactory $serviceFactory;

    protected HttpClientOptions $httpClientOptions;

    protected bool $configLoaded = false;

    public function __construct(ServiceFactory $serviceFactory, HttpClientOptions $httpClientOptions)
    {
        $this->serviceFactory = $serviceFactory;
        $this->httpClientOptions = $httpClientOptions;
        $this->loadConfig();
    }

    protected function loadConfig()
    {
        if (!$this->configLoaded && is_array($service = config('gateway.services'))) {
            $this->parseServices($service);
            $this->configLoaded = true;
        }
    }

    protected function parseServices(array $services)
    {
        collect($services)->each(function ($serviceData, $id) {
            if (empty($id) || is_numeric($id) || $this->has($id)) {
                throw new InvalidServiceException('Service id is empty, invalid or already registered.');
            }

            try {
                $serviceData['id'] = $id;
                if (!isset($serviceData['routes']) || !is_array($serviceData['routes'])) {
                    $serviceData['routes'] = [];
                }
                $serviceRoutesData = $serviceData['routes'];
                unset($serviceData['routes']);
                $service = $this->serviceFactory->createService(
                    $serviceData,
                    $this->parseServiceRoutes($serviceRoutesData)
                );

                $this->add($service);
                Log::debug('Service created.', $service->toArray());
            } catch (Exception $e) {
                Log::error('Error when creating service.', ['service'=>$serviceData, 'message'=>$e->getMessage()]);
            }
        });
    }

    protected function parseServiceRoutes(array $routes): array
    {
        return array_map(function(array $route) {
            return $this->serviceFactory->createServiceRouteCallback($this->serviceFactory->createServiceRouteOptions($route));
        }, $routes);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function add(ServiceContract $service): self
    {
        $this->items[$service->id()] = $service;
        return $this;
    }

    public function get(string $id): ?ServiceContract
    {
        return isset($this->items[$id]) ? $this->items[$id] : null;
    }

    public function has(string $id): bool
    {
        return null !== $this->get($id);
    }

    public function all(): Collection
    {
        return collect($this->items);
    }
}
