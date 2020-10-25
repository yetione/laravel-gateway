<?php


namespace Yetione\Gateway\HttpClient\Contracts;


use Yetione\Gateway\User;
use GuzzleHttp\Cookie\CookieJarInterface;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\HeaderBag;

interface HttpRequestContract
{
    public function method(): string;

    public function url(): string;

    public function user(): ?User;

    public function setUser(?User $user): HttpRequestContract;

    public function headers(): HeaderBag;

    public function setHeaders(HeaderBag $headers): HttpRequestContract;

    public function options(): Collection;

    public function setOptions(Collection $options): HttpRequestContract;

    public function cookie(): CookieJarInterface;

    public function setCookie(CookieJarInterface $cookieJar): HttpRequestContract;

    public function files(): array;

    public function setFiles(array $files): HttpRequestContract;

    public function buildOptions(): array;
}
