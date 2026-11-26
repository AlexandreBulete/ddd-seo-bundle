<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Command\ChangeSeoSettings;

use AlexandreBulete\DddSeoBundle\Domain\Model\SeoSettings;
use AlexandreBulete\DddFoundation\Application\Authorization\Permission;
use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;

/**
 * Changes the site-wide SEO rules.
 *
 * @implements CommandInterface<SeoSettings>
 */
#[Permission('seo.configure')]
final readonly class ChangeSeoSettingsCommand implements CommandInterface
{
    public function __construct(
        public bool $indexing,
        public ?string $robotsRules,
        public ?string $titlePattern,
        public ?string $personName = null,
        public ?string $personUrl = null,
        /** @var list<string> */
        public array $personSameAs = [],
    ) {}
}
