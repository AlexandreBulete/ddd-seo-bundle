<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\State\Provider;

use AlexandreBulete\DddSeoBundle\Application\Query\FindRedirect\FindRedirectQuery;
use AlexandreBulete\DddSeoBundle\Domain\Model\Redirect;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\RedirectId;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource\RedirectResource;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\RequestOption;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProviderInterface;

final readonly class RedirectBulkItemsProvider implements ProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {}

    /**
     * @return list<RedirectResource>
     */
    public function provide(Operation $operation, Context $context): array
    {
        /** @var list<string> $ids */
        $ids = $context->get(RequestOption::class)?->request()->request->all('ids') ?? [];

        $resources = [];
        foreach ($ids as $id) {
            /** @var Redirect $redirect */
            $redirect = $this->queryBus->ask(new FindRedirectQuery(RedirectId::fromString($id)));
            $resources[] = RedirectResource::fromModel($redirect);
        }

        return $resources;
    }
}
