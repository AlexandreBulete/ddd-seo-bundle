<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Symfony\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * No other redirect starts from the same address.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class FreeRedirectSource extends Constraint
{
    public string $message = 'seo.redirect.source_taken';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
