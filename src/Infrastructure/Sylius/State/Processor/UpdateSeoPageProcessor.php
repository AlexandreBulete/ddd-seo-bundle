<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\State\Processor;

use AlexandreBulete\DddSeoBundle\Application\Command\SaveSeoEntry\SaveSeoEntryCommand;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoTarget;
use AlexandreBulete\DddSeoBundle\Infrastructure\Config\SeoKinds;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource\SeoPageResource;
use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;
use Webmozart\Assert\Assert;

final readonly class UpdateSeoPageProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {}

    public function process(mixed $data, Operation $operation, Context $context): SeoPageResource
    {
        Assert::isInstanceOf($data, SeoPageResource::class);

        $this->commandBus->dispatch(new SaveSeoEntryCommand(new SeoTarget(SeoKinds::ROUTE, $data->id), $data->seo->toFields()));

        return $data;
    }
}
