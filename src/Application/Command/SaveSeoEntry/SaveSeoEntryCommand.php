<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Command\SaveSeoEntry;

use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoFields;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoTarget;
use AlexandreBulete\DddFoundation\Application\Authorization\Permission;
use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;

/**
 * Sets the SEO of one page by hand. All empty: the page follows its template again.
 *
 * @implements CommandInterface<void>
 */
#[Permission('seo.edit')]
final readonly class SaveSeoEntryCommand implements CommandInterface
{
    public function __construct(
        public SeoTarget $target,
        public SeoFields $fields,
    ) {}
}
