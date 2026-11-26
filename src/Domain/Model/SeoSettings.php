<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Domain\Model;

use AlexandreBulete\DddSeoBundle\Domain\Event\SeoSettingsChanged;
use AlexandreBulete\DddFoundation\Domain\Model\RecordsEvents;

/**
 * The site-wide rules: whether engines may index the site at all (off on a
 * staging copy), the rules of robots.txt, and the title pattern of a page
 * whose kind has none.
 */
final class SeoSettings
{
    use RecordsEvents;

    public const string KEY = 'default';
    public const string DEFAULT_TITLE = '{title} — {site}';
    public const string DEFAULT_ROBOTS = "User-agent: *\nDisallow: /admin/";
    public const int ROBOTS_MAX_LENGTH = 5000;
    public const int TITLE_MAX_LENGTH = 200;

    private function __construct(
        public private(set) string $key,
        public private(set) bool $indexing,
        public private(set) string $robotsRules,
        public private(set) string $titlePattern,
        public private(set) ?\DateTimeImmutable $updatedAt,
    ) {}

    public static function defaults(): self
    {
        return new self(self::KEY, true, self::DEFAULT_ROBOTS, self::DEFAULT_TITLE, null);
    }

    public function change(bool $indexing, ?string $robotsRules, ?string $titlePattern, \DateTimeImmutable $at): void
    {
        $robotsRules = trim(str_replace("\r\n", "\n", (string) $robotsRules));
        $robotsRules = $robotsRules === '' ? self::DEFAULT_ROBOTS : $robotsRules;
        if (mb_strlen($robotsRules) > self::ROBOTS_MAX_LENGTH) {
            throw new \InvalidArgumentException(sprintf('robots.txt rules have %d characters at most.', self::ROBOTS_MAX_LENGTH));
        }
        if (preg_match('/^\s*sitemap\s*:/im', $robotsRules) === 1) {
            throw new \InvalidArgumentException('robots.txt rules do not declare the sitemap: it is added on its own.');
        }
        $titlePattern = trim((string) $titlePattern);
        $titlePattern = $titlePattern === '' ? self::DEFAULT_TITLE : $titlePattern;
        if (mb_strlen($titlePattern) > self::TITLE_MAX_LENGTH) {
            throw new \InvalidArgumentException(sprintf('The title pattern has %d characters at most.', self::TITLE_MAX_LENGTH));
        }
        if ($indexing === $this->indexing && $robotsRules === $this->robotsRules && $titlePattern === $this->titlePattern) {
            return;
        }
        $this->indexing = $indexing;
        $this->robotsRules = $robotsRules;
        $this->titlePattern = $titlePattern;
        $this->updatedAt = $at;
        $this->recordEvent(new SeoSettingsChanged());
    }
}
