<?php


namespace Yetione\Gateway\Routing\Contracts;


interface ServiceContract
{
    public function id(): string;

    public function hostname(): array;

    public function resolveHost(): string;

    public function getHostResolver(): HostResolverContract;

    public function path(): string;

    public function method(): array;

    public function public(): bool;

    public function requestOptions(): array;

    /**
     * @return ServiceRouteContract[]
     */
    public function routes(): array;

    public function defaultRoute(): ?ServiceRouteContract;

    public function toArray(): array;
}
