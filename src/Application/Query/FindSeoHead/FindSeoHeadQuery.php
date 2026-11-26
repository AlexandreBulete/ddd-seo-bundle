<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Query\FindSeoHead;

use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoTarget;
use AlexandreBulete\DddFoundation\Application\Authorization\PublicAccess;
use AlexandreBulete\DddFoundation\Application\Query\QueryInterface;

/**
 * @implements QueryInterface<SeoHead>
 */
#[PublicAccess]
final readonly class FindSeoHeadQuery implements QueryInterface
{
    public function __construct(
        public SeoTarget $target,
    ) {}
}
