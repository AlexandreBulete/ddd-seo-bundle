<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Command\FollowRedirect;

use AlexandreBulete\DddFoundation\Application\Authorization\PublicAccess;
use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;

/**
 * A visitor asks for an address that does not answer: where to send them, if anywhere. Counts the visit.
 *
 * @implements CommandInterface<?string>
 */
#[PublicAccess]
final readonly class FollowRedirectCommand implements CommandInterface
{
    public function __construct(
        public string $path,
    ) {}
}
