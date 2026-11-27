<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Doctrine\Type;

use AlexandreBulete\DddSeoBundle\Domain\ValueObject\RedirectId;
use AlexandreBulete\DddDoctrineBridge\Type\GuidType;

final class RedirectIdType extends GuidType
{
    public const string NAME = 'seo_redirect_id';

    protected string $name = self::NAME;

    protected string $voClass = RedirectId::class;
}
