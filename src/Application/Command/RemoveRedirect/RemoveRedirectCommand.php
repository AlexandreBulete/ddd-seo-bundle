<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Command\RemoveRedirect;

use AlexandreBulete\DddSeoBundle\Domain\ValueObject\RedirectId;
use AlexandreBulete\DddFoundation\Application\Authorization\Permission;
use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;

/**
 * Removes a redirect: the old address answers "not found" again.
 *
 * @implements CommandInterface<void>
 */
#[Permission('seo.redirect')]
final readonly class RemoveRedirectCommand implements CommandInterface
{
    public function __construct(
        public RedirectId $id,
    ) {}
}
