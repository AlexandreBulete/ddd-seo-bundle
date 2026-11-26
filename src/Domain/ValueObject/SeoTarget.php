<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Domain\ValueObject;

/**
 * What a page is about, for the search engines: a kind of content the
 * application declares (`review`, `post`, `route` for its fixed pages…) and
 * the content's reference — its id, or the route name of a fixed page.
 */
final readonly class SeoTarget
{
    public const string KIND_PATTERN = '[a-z][a-z0-9_]{0,29}';
    public const int REFERENCE_MAX_LENGTH = 150;

    public function __construct(
        public string $kind,
        public string $reference,
    ) {
        if (preg_match('/^' . self::KIND_PATTERN . '$/', $kind) !== 1) {
            throw new \InvalidArgumentException(sprintf('A SEO target kind is lowercase letters, digits and underscores, "%s" given.', $kind));
        }
        if ($reference === '' || mb_strlen($reference) > self::REFERENCE_MAX_LENGTH) {
            throw new \InvalidArgumentException(sprintf('A SEO target reference has 1 to %d characters.', self::REFERENCE_MAX_LENGTH));
        }
    }

    public function __toString(): string
    {
        return $this->kind . ':' . $this->reference;
    }
}
