<?php


namespace Yetione\Gateway\Options\Contracts;


use Yetione\Gateway\Exceptions\OptionNotSetException;
use Illuminate\Support\Collection;

interface OptionsContract
{
    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, $default=null);

    /**
     * @param string $key
     * @return mixed
     * @throws OptionNotSetException
     */
    public function getStrict(string $key);

    /**
     * @return Collection
     */
    public function all(): Collection;

    /**
     * @return ResolverContract
     */
    public function getResolver(): ResolverContract;

    /**
     * @param array $options
     * @return OptionsContract
     */
    public function setOptions(array $options): OptionsContract;
}
