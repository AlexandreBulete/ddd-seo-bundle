<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Domain\Model;

use AlexandreBulete\DddSeoBundle\Domain\Event\SeoSettingsChanged;
use AlexandreBulete\DddFoundation\Domain\Model\RecordsEvents;

/**
 * The site-wide rules: whether engines may index the site at all (off on a
 * staging copy), the rules of robots.txt, the title pattern of a page whose
 * kind has none, and who writes the site — the author and publisher of its
 * structured data.
 */
final class SeoSettings
{
    use RecordsEvents;

    public const string KEY = 'default';
    public const string DEFAULT_TITLE = '{title} — {site}';
    public const string DEFAULT_ROBOTS = "User-agent: *\nDisallow: /admin/";
    public const int ROBOTS_MAX_LENGTH = 5000;
    public const int TITLE_MAX_LENGTH = 200;
    public const int PERSON_NAME_MAX_LENGTH = 100;
    public const int URL_MAX_LENGTH = 500;
    public const int SAME_AS_MAX = 10;

    private function __construct(
        public private(set) string $key,
        public private(set) bool $indexing,
        public private(set) string $robotsRules,
        public private(set) string $titlePattern,
        public private(set) ?\DateTimeImmutable $updatedAt,
        public private(set) ?string $personName = null,
        public private(set) ?string $personUrl = null,
        /** @var list<string>|null */
        public private(set) ?array $personSameAs = null,
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

    /**
     * The person behind the site: their name, the page about them (a path of
     * this site or an address), their profiles elsewhere. No name: no person
     * in the structured data.
     *
     * @param list<string> $sameAs
     */
    public function describePerson(?string $name, ?string $url, array $sameAs, \DateTimeImmutable $at): void
    {
        $name = trim((string) $name);
        $name = $name === '' ? null : $name;
        if ($name !== null && mb_strlen($name) > self::PERSON_NAME_MAX_LENGTH) {
            throw new \InvalidArgumentException(sprintf('A name has %d characters at most.', self::PERSON_NAME_MAX_LENGTH));
        }
        $url = trim((string) $url);
        $url = $url === '' ? null : self::url($url, true);
        $links = [];
        foreach ($sameAs as $link) {
            $link = trim($link);
            if ($link !== '' && !in_array($link, $links, true)) {
                $links[] = self::url($link, false);
            }
        }
        if (count($links) > self::SAME_AS_MAX) {
            throw new \InvalidArgumentException(sprintf('%d profiles at most.', self::SAME_AS_MAX));
        }
        $links = $links === [] ? null : $links;
        if ($name === $this->personName && $url === $this->personUrl && $links === $this->personSameAs) {
            return;
        }
        $this->personName = $name;
        $this->personUrl = $url;
        $this->personSameAs = $links;
        $this->updatedAt = $at;
        $this->recordEvent(new SeoSettingsChanged());
    }

    private static function url(string $url, bool $pathAllowed): string
    {
        $valid = ($pathAllowed && str_starts_with($url, '/') && !str_starts_with($url, '//'))
            || (preg_match('#^https?://#i', $url) === 1 && filter_var($url, \FILTER_VALIDATE_URL) !== false);
        if (!$valid || mb_strlen($url) > self::URL_MAX_LENGTH || preg_match('/\s/', $url) === 1) {
            throw new \InvalidArgumentException(sprintf($pathAllowed ? '"%s" is neither an http(s) address nor a path of this site.' : '"%s" is not an http(s) address.', $url));
        }

        return $url;
    }
}
