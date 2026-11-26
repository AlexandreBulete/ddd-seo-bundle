<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Rendering;

/**
 * Fills `{name}` placeholders. A missing or empty variable leaves no trace:
 * the separators around it are tidied (`{title}, {author}` without author
 * gives the title alone).
 */
final class Pattern
{
    private const string SEPARATORS = " \t,;:·|–—-";

    /**
     * @param array<string, mixed> $variables
     */
    public static function fill(string $pattern, array $variables): string
    {
        $filled = (string) preg_replace_callback('/\{\s*([a-z][a-z0-9_]*)\s*\}/i', static function (array $match) use ($variables): string {
            $value = $variables[$match[1]] ?? null;

            return is_scalar($value) || $value instanceof \Stringable ? trim((string) $value) : '';
        }, $pattern);

        // A separator left alone by an empty variable: ", —" → " —".
        $filled = (string) preg_replace('/\s*[,;:]\s*(?=[–—|·-]|$)/u', ' ', $filled);
        $filled = (string) preg_replace('/\s+/u', ' ', $filled);

        return trim($filled, self::SEPARATORS);
    }

    /**
     * At most `$max` characters, cut between words.
     */
    public static function shorten(string $text, int $max): string
    {
        $text = trim((string) preg_replace('/\s+/u', ' ', $text));
        if (mb_strlen($text) <= $max) {
            return $text;
        }
        $cut = mb_substr($text, 0, $max - 1);
        $space = mb_strrpos($cut, ' ');

        return rtrim($space !== false && $space > $max / 2 ? mb_substr($cut, 0, $space) : $cut, self::SEPARATORS . '.') . '…';
    }
}
