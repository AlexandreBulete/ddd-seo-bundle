<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Command\AddRedirect;

use AlexandreBulete\DddSeoBundle\Domain\Model\Redirect;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\RedirectSource;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\RedirectTarget;
use AlexandreBulete\DddFoundation\Application\Authorization\Permission;
use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;

/**
 * Sends an address to another, for good. From an address already redirected: retargets it. Those leading to the old address now lead to the new one; one starting from the new address is dropped, the page answers again.
 *
 * @implements CommandInterface<Redirect>
 */
#[Permission('seo.redirect')]
final readonly class AddRedirectCommand implements CommandInterface
{
    public function __construct(
        public RedirectSource $source,
        public RedirectTarget $target,
        public bool $automatic = false,
    ) {}
}
