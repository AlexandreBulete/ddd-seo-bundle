<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Domain\ValueObject;

/**
 * Where a redirect leads: a path of this site, or an http(s) address.
 */
final readonly class RedirectTarget implements \Stringable
{
    public const int MAX_LENGTH = 500;

    public string $url;

    public function __construct(string $url)
    {
        $url = trim($url);
        $valid = (str_starts_with($url, '/') && !str_starts_with($url, '//'))
            || (preg_match('#^https?://#i', $url) === 1 && filter_var($url, \FILTER_VALIDATE_URL) !== false);
        if (!$valid || mb_strlen($url) > self::MAX_LENGTH || preg_match('/\s/', $url) === 1) {
            throw new \InvalidArgumentException(sprintf('A redirect leads to a path of this site or an http(s) address, "%s" given.', $url));
        }
        $this->url = $url;
    }

    /**
     * The path, when it is one of this site's: a redirect that started from
     * here no longer applies once it is the target.
     */
    public function path(): ?string
    {
        if (!str_starts_with($this->url, '/')) {
            return null;
        }
        try {
            return (new RedirectSource($this->url))->path;
        } catch (\InvalidArgumentException) {
            return null;
        }
    }

    public function __toString(): string
    {
        return $this->url;
    }
}
