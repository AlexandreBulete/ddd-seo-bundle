<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Domain\Exception;

final class RedirectSourceTakenException extends \DomainException
{
    public function __construct(
        public readonly string $source,
    ) {
        parent::__construct(sprintf('A redirect already starts from "%s".', $source));
    }
}
