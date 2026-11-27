<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\State\Processor;

use AlexandreBulete\DddSeoBundle\Application\Command\ChangeRedirect\ChangeRedirectCommand;
use AlexandreBulete\DddSeoBundle\Domain\Model\Redirect;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\RedirectId;
use Symfony\Component\Uid\Ulid;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource\RedirectResource;
use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;
use Webmozart\Assert\Assert;

final readonly class UpdateRedirectProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {}

    public function process(mixed $data, Operation $operation, Context $context): RedirectResource
    {
        Assert::isInstanceOf($data, RedirectResource::class);
        Assert::notNull($data->id);
        /** @var Redirect $redirect */
        $redirect = $this->commandBus->dispatch(new ChangeRedirectCommand(RedirectId::fromUlid(Ulid::fromString($data->id->toRfc4122())), $data->sourcePath(), $data->targetUrl()));

        return RedirectResource::fromModel($redirect);
    }
}
