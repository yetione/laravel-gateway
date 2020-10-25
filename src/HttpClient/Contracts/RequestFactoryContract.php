<?php


namespace Yetione\Gateway\HttpClient\Contracts;


interface RequestFactoryContract
{
    public function make(string $method, string $uri, array $options=[]): HttpRequestContract;

    public function requestClass(): string;

    public function setRequestClass(string $requestClass): RequestFactoryContract;
}
