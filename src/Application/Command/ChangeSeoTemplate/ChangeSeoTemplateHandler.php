<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Command\ChangeSeoTemplate;

use AlexandreBulete\DddSeoBundle\Domain\Exception\UnknownSeoKindException;
use AlexandreBulete\DddSeoBundle\Domain\Model\SeoTemplate;
use AlexandreBulete\DddSeoBundle\Domain\Repository\SeoTemplateRepositoryInterface;
use AlexandreBulete\DddSeoBundle\Domain\Service\SeoKindCatalogInterface;
use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use Psr\Clock\ClockInterface;

#[AsCommandHandler]
final readonly class ChangeSeoTemplateHandler
{
    public function __construct(
        private SeoTemplateRepositoryInterface $templates,
        private SeoKindCatalogInterface $kinds,
        private ClockInterface $clock,
    ) {}

    public function __invoke(ChangeSeoTemplateCommand $command): SeoTemplate
    {
        if (!$this->kinds->has($command->kind)) {
            throw new UnknownSeoKindException($command->kind);
        }

        $template = $this->templates->get($command->kind);
        $template->change($command->title, $command->description, $this->clock->now());
        $this->templates->save($template);

        return $template;
    }
}
