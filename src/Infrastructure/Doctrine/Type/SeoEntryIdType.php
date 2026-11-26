<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Doctrine\Type;

use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoEntryId;
use AlexandreBulete\DddDoctrineBridge\Type\GuidType;

final class SeoEntryIdType extends GuidType
{
    public const string NAME = 'seo_entry_id';

    protected string $name = self::NAME;

    protected string $voClass = SeoEntryId::class;
}
