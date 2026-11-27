<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Command\ChangeRedirect;

use AlexandreBulete\DddSeoBundle\Domain\Model\Redirect;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\RedirectId;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\RedirectSource;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\RedirectTarget;
use AlexandreBulete\DddFoundation\Application\Authorization\Permission;
use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;

/**
 * Changes where a redirect starts from, or leads.
 *
 * @implements CommandInterface<Redirect>
 */
#[Permission('seo.redirect')]
final readonly class ChangeRedirectCommand implements CommandInterface
{
    public function __construct(
        public RedirectId $id,
        public RedirectSource $source,
        public RedirectTarget $target,
    ) {}
}
