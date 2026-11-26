<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Tests\Unit\Infrastructure;

use AlexandreBulete\DddSeoBundle\Infrastructure\Rendering\Pattern;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PatternTest extends TestCase
{
    #[Test]
    public function variablesAreFilled(): void
    {
        self::assertSame(
            'A Book, Ann Author: review — The site',
            Pattern::fill('{title}, { author }: review — {site}', ['title' => 'A Book', 'author' => 'Ann Author', 'site' => 'The site']),
        );
    }

    #[Test]
    public function aMissingVariableLeavesNoStraySeparator(): void
    {
        self::assertSame('Un livre — Le site', Pattern::fill('{title}, {author} — {site}', ['title' => 'Un livre', 'site' => 'Le site']));
        self::assertSame('Un livre', Pattern::fill('{title} — {site}', ['title' => 'Un livre']));
        self::assertSame('', Pattern::fill('{unknown}', []));
    }

    #[Test]
    public function aLongTextIsCutBetweenWords(): void
    {
        $short = Pattern::shorten('Un hiver au bord de la mer, une maison et trois sœurs.', 30);

        self::assertSame('Un hiver au bord de la mer…', $short);
        self::assertLessThanOrEqual(30, mb_strlen($short));
        self::assertSame('Court.', Pattern::shorten(" Court.\n", 30));
    }
}
