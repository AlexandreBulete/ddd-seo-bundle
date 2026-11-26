<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Domain\Repository;

use AlexandreBulete\DddSeoBundle\Domain\Model\SeoSettings;

interface SeoSettingsRepositoryInterface
{
    /**
     * The saved settings, or the defaults.
     */
    public function get(): SeoSettings;

    public function save(SeoSettings $settings): void;
}
