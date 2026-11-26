<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\State\Provider;

use AlexandreBulete\DddSeoBundle\Application\Query\FindSeoEntry\FindSeoEntryQuery;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoFields;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoTarget;
use AlexandreBulete\DddSeoBundle\Infrastructure\Config\SeoKinds;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource\SeoPageResource;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource\SeoResourceFactory;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\RequestOption;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProviderInterface;

/**
 * A declared route only: anything else is not found.
 */
final readonly class SeoPageItemProvider implements ProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private SeoKinds $kinds,
        private SeoResourceFactory $resources,
    ) {}

    public function provide(Operation $operation, Context $context): ?SeoPageResource
    {
        $route = $context->get(RequestOption::class)?->request()->attributes->getString('id') ?? '';
        if (!isset($this->kinds->routes()[$route])) {
            return null;
        }

        /** @var SeoFields $fields */
        $fields = $this->queryBus->ask(new FindSeoEntryQuery(new SeoTarget(SeoKinds::ROUTE, $route)));

        return $this->resources->page($route, $fields);
    }
}
