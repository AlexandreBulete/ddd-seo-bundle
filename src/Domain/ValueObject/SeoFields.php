<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Domain\ValueObject;

/**
 * What an editor sets by hand for one page. Every text is optional: an empty
 * one falls back to the kind's template, then to the content itself.
 */
final readonly class SeoFields
{
    public const int TITLE_MAX_LENGTH = 120;
    public const int DESCRIPTION_MAX_LENGTH = 320;
    public const int CANONICAL_MAX_LENGTH = 500;

    public ?string $title;
    public ?string $description;
    public ?string $canonical;
    public ?string $shareTitle;
    public ?string $shareDescription;

    public function __construct(
        ?string $title = null,
        ?string $description = null,
        ?string $canonical = null,
        public bool $noindex = false,
        ?string $shareTitle = null,
        ?string $shareDescription = null,
    ) {
        $this->title = self::text($title, 'title', self::TITLE_MAX_LENGTH);
        $this->description = self::text($description, 'description', self::DESCRIPTION_MAX_LENGTH);
        $this->canonical = self::canonical($canonical);
        $this->shareTitle = self::text($shareTitle, 'share title', self::TITLE_MAX_LENGTH);
        $this->shareDescription = self::text($shareDescription, 'share description', self::DESCRIPTION_MAX_LENGTH);
    }

    public function isEmpty(): bool
    {
        return $this->title === null && $this->description === null && $this->canonical === null
            && !$this->noindex && $this->shareTitle === null && $this->shareDescription === null;
    }

    public function equals(self $other): bool
    {
        return $this->title === $other->title
            && $this->description === $other->description
            && $this->canonical === $other->canonical
            && $this->noindex === $other->noindex
            && $this->shareTitle === $other->shareTitle
            && $this->shareDescription === $other->shareDescription;
    }

    private static function text(?string $value, string $what, int $max): ?string
    {
        $value = $value === null ? '' : trim((string) preg_replace('/\s+/u', ' ', $value));
        if ($value === '') {
            return null;
        }
        if (mb_strlen($value) > $max) {
            throw new \InvalidArgumentException(sprintf('A SEO %s has %d characters at most.', $what, $max));
        }

        return $value;
    }

    /**
     * An absolute http(s) address, or a path of this site.
     */
    private static function canonical(?string $value): ?string
    {
        $value = $value === null ? '' : trim($value);
        if ($value === '') {
            return null;
        }
        $valid = (str_starts_with($value, '/') && !str_starts_with($value, '//'))
            || (preg_match('#^https?://#i', $value) === 1 && filter_var($value, \FILTER_VALIDATE_URL) !== false);
        if (!$valid || mb_strlen($value) > self::CANONICAL_MAX_LENGTH || preg_match('/\s/', $value) === 1) {
            throw new \InvalidArgumentException('A canonical address is an absolute http(s) URL or a path starting with "/".');
        }

        return $value;
    }
}
