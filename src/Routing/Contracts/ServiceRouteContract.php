<?php


namespace Yetione\Gateway\Routing\Contracts;


interface ServiceRouteContract
{
    public function id(): string;

    public function service(): ServiceContract;

    public function method(): array;

    public function path(): string;

    public function alias(): ?string;

    public function requestOptions(): array;

    public function public(): bool;

    public function middleware(): array;

    public function routePath(): string;

    public function serviceUri(array $parametersJar=[]): string;

    public function servicePath(): string;

    public function requestClass(): string;

    public function toArray(): array;
}
