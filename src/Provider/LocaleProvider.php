<?php

declare(strict_types=1);

namespace Ithis\Bundle\EntityTranslation\Provider;

use Ithis\Bundle\EntityTranslation\Contract\Provider\LocaleProviderInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class LocaleProvider implements LocaleProviderInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private ParameterBagInterface $parameterBag,
        private ?TranslatorInterface $translator
    ) {
    }

    public function provideCurrentLocale(): ?string
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if (! $currentRequest instanceof Request) {
            return null;
        }

        $currentLocale = $currentRequest->getLocale();
        if ($currentLocale !== '') {
            return $currentLocale;
        }

        return $this->translator?->getLocale();
    }

    public function provideFallbackLocale(): ?string
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if ($currentRequest !== null) {
            return $currentRequest->getDefaultLocale();
        }

        try {
            if ($this->parameterBag->has('locale')) {
                return (string) $this->parameterBag->get('locale');
            }

            return (string) $this->parameterBag->get('kernel.default_locale');
        } catch (ParameterNotFoundException | InvalidArgumentException) {
            return null;
        }
    }
}
