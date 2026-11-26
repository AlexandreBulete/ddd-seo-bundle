<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Domain\Exception;

final class UnknownSeoKindException extends \DomainException
{
    public function __construct(
        public readonly string $kind,
    ) {
        parent::__construct(sprintf('No kind of page "%s" is declared.', $kind));
    }
}
