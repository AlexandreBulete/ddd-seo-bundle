<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Command\ChangeSeoTemplate;

use AlexandreBulete\DddSeoBundle\Domain\Model\SeoTemplate;
use AlexandreBulete\DddFoundation\Application\Authorization\Permission;
use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;

/**
 * Changes how the title and description of a kind of content are written.
 *
 * @implements CommandInterface<SeoTemplate>
 */
#[Permission('seo.configure')]
final readonly class ChangeSeoTemplateCommand implements CommandInterface
{
    public function __construct(
        public string $kind,
        public ?string $title,
        public ?string $description,
        public bool $structuredData = true,
    ) {}
}
