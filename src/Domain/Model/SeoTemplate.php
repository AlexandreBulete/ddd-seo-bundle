<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Domain\Model;

use AlexandreBulete\DddSeoBundle\Domain\Event\SeoTemplateChanged;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoTarget;
use AlexandreBulete\DddFoundation\Domain\Model\RecordsEvents;

/**
 * How the title and description of a kind of content are written when an
 * editor set none: patterns with `{variables}` the application fills
 * (`{title}, {author}: review — {site}`). Null: the application's
 * default for that kind.
 */
final class SeoTemplate
{
    use RecordsEvents;

    public const int PATTERN_MAX_LENGTH = 200;

    private function __construct(
        public private(set) string $kind,
        public private(set) ?string $title,
        public private(set) ?string $description,
        public private(set) ?\DateTimeImmutable $updatedAt,
    ) {}

    public static function defaults(string $kind): self
    {
        new SeoTarget($kind, 'x'); // validates the kind

        return new self($kind, null, null, null);
    }

    public function change(?string $title, ?string $description, \DateTimeImmutable $at): void
    {
        $title = self::pattern($title);
        $description = self::pattern($description);
        if ($title === $this->title && $description === $this->description) {
            return;
        }
        $this->title = $title;
        $this->description = $description;
        $this->updatedAt = $at;
        $this->recordEvent(new SeoTemplateChanged($this->kind));
    }

    private static function pattern(?string $value): ?string
    {
        $value = $value === null ? '' : trim((string) preg_replace('/\s+/u', ' ', $value));
        if ($value === '') {
            return null;
        }
        if (mb_strlen($value) > self::PATTERN_MAX_LENGTH) {
            throw new \InvalidArgumentException(sprintf('A SEO pattern has %d characters at most.', self::PATTERN_MAX_LENGTH));
        }

        return $value;
    }
}
