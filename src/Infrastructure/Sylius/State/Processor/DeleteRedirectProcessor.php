<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\State\Processor;

use AlexandreBulete\DddSeoBundle\Application\Command\RemoveRedirect\RemoveRedirectCommand;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\RedirectId;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource\RedirectResource;
use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;
use Symfony\Component\Uid\Ulid;
use Webmozart\Assert\Assert;

final readonly class DeleteRedirectProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {}

    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        foreach (is_array($data) ? $data : [$data] as $resource) {
            Assert::isInstanceOf($resource, RedirectResource::class);
            Assert::notNull($resource->id);
            $this->commandBus->dispatch(new RemoveRedirectCommand(RedirectId::fromUlid(Ulid::fromString($resource->id->toRfc4122()))));
        }

        return null;
    }
}
