<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Query\FindRedirects;

use AlexandreBulete\DddSeoBundle\Domain\Model\Redirect;
use AlexandreBulete\DddFoundation\Application\Authorization\Permission;
use AlexandreBulete\DddFoundation\Application\Query\QueryInterface;

/**
 * The redirects, by old address.
 *
 * @implements QueryInterface<list<Redirect>>
 */
#[Permission('seo.redirect')]
final readonly class FindRedirectsQuery implements QueryInterface
{
    public function __construct(
        public ?string $search = null,
    ) {}
}
