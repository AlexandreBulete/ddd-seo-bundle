<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\State\Provider;

use AlexandreBulete\DddSeoBundle\Application\Query\FindSeoTemplate\FindSeoTemplateQuery;
use AlexandreBulete\DddSeoBundle\Domain\Model\SeoTemplate;
use AlexandreBulete\DddSeoBundle\Infrastructure\Config\SeoKinds;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource\SeoResourceFactory;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource\SeoTemplateResource;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\RequestOption;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProviderInterface;

final readonly class SeoTemplateItemProvider implements ProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private SeoKinds $kinds,
        private SeoResourceFactory $resources,
    ) {}

    public function provide(Operation $operation, Context $context): ?SeoTemplateResource
    {
        $kind = $context->get(RequestOption::class)?->request()->attributes->getString('id') ?? '';
        if (!$this->kinds->has($kind)) {
            return null;
        }

        /** @var SeoTemplate $template */
        $template = $this->queryBus->ask(new FindSeoTemplateQuery($kind));

        return $this->resources->template($template);
    }
}
