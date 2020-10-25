<?php

namespace Yetione\Gateway\HttpClient;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Collection;

class RestBatchResponse
{
    /**
     * @var array
     */
    protected array $responses = [];

    /**
     * @var array
     */
    protected array $codes = [];

    /**
     * @var int
     */
    protected int $failures = 0;

    /**
     * @var bool
     */
    protected bool $hasCritical = false;

    /**
     * @param string $alias
     * @param Response $response
     */
    public function addSuccessfulAction($alias, Response $response)
    {
        $this->addAction($alias, (string)$response->getBody(), $response->getStatusCode());
    }

    /**
     * @param string $alias
     * @param Response $response
     */
    public function addFailedAction($alias, Response $response)
    {
        $this->addAction($alias, (string)$response->getBody(), $response->getStatusCode());
        $this->failures++;
    }

    /**
     * @param string $alias
     * @param $content
     * @param $code
     */
    private function addAction($alias, $content, $code)
    {
        $this->responses[$alias] = $content;
        $this->codes[$alias] = $code;
    }

    /**
     * @return Collection
     */
    public function getResponses()
    {
        return collect($this->responses)->map(function ($response) {
            return json_decode($response, true);
        });
    }

    /**
     * @return array
     */
    public function exportParameters()
    {
        return collect(array_keys($this->responses))->reduce(function ($carry, $alias) {
            $output = [];
            $decoded = json_decode($this->responses[$alias], true);
            if ($decoded === null) return $carry;

            foreach ($decoded as $key => $value) {
                $output[$alias . '%' . $key] = $value;
            }

            return array_merge($carry, $output);
        }, []);
    }

    /**
     * @return bool
     */
    public function hasFailedRequests()
    {
        return $this->failures > 0;
    }

    /**
     * @param bool $critical
     * @return $this
     */
    public function setCritical($critical)
    {
        $this->hasCritical = $critical;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasCriticalActions()
    {
        return $this->hasCritical;
    }
}
