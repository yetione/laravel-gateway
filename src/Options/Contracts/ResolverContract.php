<?php


namespace Yetione\Gateway\Options\Contracts;


use Illuminate\Support\Collection;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\NoSuchOptionException;
use Symfony\Component\OptionsResolver\Exception\OptionDefinitionException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface ResolverContract
{
    /**
     * @param array $options
     * @return Collection
     *
     * @throws UndefinedOptionsException
     * @throws InvalidOptionsException
     * @throws MissingOptionsException
     * @throws OptionDefinitionException
     * @throws NoSuchOptionException
     * @throws AccessException
     *
     * @see OptionsResolver::resolve()
     */
    public function process(array $options=[]): Collection;
}
