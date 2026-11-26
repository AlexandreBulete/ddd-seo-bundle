<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\State\Processor;

use AlexandreBulete\DddSeoBundle\Application\Command\ChangeSeoTemplate\ChangeSeoTemplateCommand;
use AlexandreBulete\DddSeoBundle\Domain\Model\SeoTemplate;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource\SeoResourceFactory;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource\SeoTemplateResource;
use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;
use Webmozart\Assert\Assert;

final readonly class UpdateSeoTemplateProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private SeoResourceFactory $resources,
    ) {}

    public function process(mixed $data, Operation $operation, Context $context): SeoTemplateResource
    {
        Assert::isInstanceOf($data, SeoTemplateResource::class);

        /** @var SeoTemplate $template */
        $template = $this->commandBus->dispatch(new ChangeSeoTemplateCommand($data->id, $data->title, $data->description));

        return $this->resources->template($template);
    }
}
