<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Domain\Exception;

final class RedirectNotFoundException extends \DomainException
{
    public function __construct(string $id)
    {
        parent::__construct(sprintf('No redirect "%s".', $id));
    }
}
