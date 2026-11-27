<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Symfony\Form;

/**
 * Variables the back-office preview cannot read from the form, the same for
 * every page: the site's name above all (`['site' => 'My site']`). Implement it
 * in the application; without one, those variables stay empty in the preview.
 */
interface SeoPreviewVariablesInterface
{
    /**
     * @return array<string, string>
     */
    public function variables(): array;
}
