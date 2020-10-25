<?php


namespace Yetione\Gateway\HttpClient\Contracts;


interface HttpClientFactoryContract
{
    public function make(array $options): HttpClientContract;

    public function client(array $options=[], bool $create=false): HttpClientContract;
}
