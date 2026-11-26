<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Domain\Repository;

use AlexandreBulete\DddSeoBundle\Domain\Model\SeoTemplate;

interface SeoTemplateRepositoryInterface
{
    /**
     * The saved template, or an empty one.
     */
    public function get(string $kind): SeoTemplate;

    public function save(SeoTemplate $template): void;
}
