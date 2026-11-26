<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\State\Processor;

use AlexandreBulete\DddSeoBundle\Application\Command\ChangeSeoSettings\ChangeSeoSettingsCommand;
use AlexandreBulete\DddSeoBundle\Domain\Model\SeoSettings;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource\SeoSettingsResource;
use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;
use Webmozart\Assert\Assert;

final readonly class UpdateSeoSettingsProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {}

    public function process(mixed $data, Operation $operation, Context $context): SeoSettingsResource
    {
        Assert::isInstanceOf($data, SeoSettingsResource::class);

        /** @var SeoSettings $settings */
        $settings = $this->commandBus->dispatch(new ChangeSeoSettingsCommand($data->indexing, $data->robotsRules, $data->titlePattern, $data->personName, $data->personUrl, $data->sameAsList()));

        return SeoSettingsResource::fromModel($settings);
    }
}
