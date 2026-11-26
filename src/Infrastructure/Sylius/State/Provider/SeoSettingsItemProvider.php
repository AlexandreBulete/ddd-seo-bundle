<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\State\Provider;

use AlexandreBulete\DddSeoBundle\Application\Query\FindSeoSettings\FindSeoSettingsQuery;
use AlexandreBulete\DddSeoBundle\Domain\Model\SeoSettings;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource\SeoSettingsResource;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProviderInterface;

/**
 * Whatever the id in the URL: there is one set of settings.
 */
final readonly class SeoSettingsItemProvider implements ProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {}

    public function provide(Operation $operation, Context $context): SeoSettingsResource
    {
        /** @var SeoSettings $settings */
        $settings = $this->queryBus->ask(new FindSeoSettingsQuery());

        return SeoSettingsResource::fromModel($settings);
    }
}
