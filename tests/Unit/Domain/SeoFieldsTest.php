<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Tests\Unit\Domain;

use AlexandreBulete\DddSeoBundle\Domain\Model\SeoSettings;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoFields;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoTarget;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SeoFieldsTest extends TestCase
{
    #[Test]
    public function blanksAreNothingAndSpacesAreTidied(): void
    {
        $fields = new SeoFields("  Un   titre\n", ' ', '');

        self::assertSame('Un titre', $fields->title);
        self::assertNull($fields->description);
        self::assertNull($fields->canonical);
        self::assertFalse((new SeoFields(noindex: true))->isEmpty());
        self::assertTrue((new SeoFields(' ', ''))->isEmpty());
    }

    #[Test]
    #[DataProvider('canonicals')]
    public function aCanonicalIsAPathOrAnHttpAddress(string $canonical, bool $valid): void
    {
        if (!$valid) {
            $this->expectException(\InvalidArgumentException::class);
        }

        self::assertSame($canonical, (new SeoFields(canonical: $canonical))->canonical);
    }

    /**
     * @return iterable<array{string, bool}>
     */
    public static function canonicals(): iterable
    {
        yield ['/reviews/a-book', true];
        yield ['https://example.com/page', true];
        yield ['//evil.example/page', false];
        yield ['javascript:alert(1)', false];
        yield ['relative/path', false];
        yield ['/with space', false];
    }

    #[Test]
    public function aTargetKindIsLowercase(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SeoTarget('Review', '1');
    }

    #[Test]
    public function robotsRulesNeverDeclareTheSitemapAndFallBackToTheDefaults(): void
    {
        $settings = SeoSettings::defaults();
        $settings->change(false, "  \r\n", '', new \DateTimeImmutable());

        self::assertFalse($settings->indexing);
        self::assertSame(SeoSettings::DEFAULT_ROBOTS, $settings->robotsRules);
        self::assertSame(SeoSettings::DEFAULT_TITLE, $settings->titlePattern);

        $this->expectException(\InvalidArgumentException::class);
        $settings->change(true, "User-agent: *\nSitemap: https://elsewhere.example/sitemap.xml", null, new \DateTimeImmutable());
    }
}
