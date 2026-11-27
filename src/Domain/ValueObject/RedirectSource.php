<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Domain\ValueObject;

/**
 * The old address a visitor or an engine still asks for: a path of this
 * site, without its query string nor a trailing slash (`/old/` and `/old`
 * are one address). Matched as written otherwise, case included.
 */
final readonly class RedirectSource implements \Stringable
{
    public const int MAX_LENGTH = 500;

    public string $path;

    public function __construct(string $path)
    {
        $path = trim($path);
        $path = explode('#', explode('?', $path, 2)[0], 2)[0];
        if (!str_starts_with($path, '/') || str_starts_with($path, '//') || preg_match('/\s/', $path) === 1) {
            throw new \InvalidArgumentException(sprintf('A redirect starts from a path of this site, "%s" given.', $path));
        }
        $path = $path === '/' ? $path : rtrim($path, '/');
        if ($path === '/' || mb_strlen($path) > self::MAX_LENGTH) {
            throw new \InvalidArgumentException('A redirect starts from a path other than the home page, 500 characters at most.');
        }
        $this->path = $path;
    }

    public function __toString(): string
    {
        return $this->path;
    }
}
