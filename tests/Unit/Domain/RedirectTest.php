<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Tests\Unit\Domain;

use AlexandreBulete\DddSeoBundle\Domain\Model\Redirect;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\RedirectId;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\RedirectSource;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\RedirectTarget;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RedirectTest extends TestCase
{
    #[Test]
    public function aSourceIsAPathWithoutQueryNorTrailingSlash(): void
    {
        self::assertSame('/blog/old', (new RedirectSource(' /blog/old/?page=2#top '))->path);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidSources(): iterable
    {
        yield 'home page' => ['/'];
        yield 'relative' => ['blog/old'];
        yield 'other host' => ['//example.com/old'];
        yield 'full address' => ['https://example.com/old'];
        yield 'space' => ['/old page'];
    }

    #[Test]
    #[DataProvider('invalidSources')]
    public function aSourceIsAPathOfThisSite(string $source): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new RedirectSource($source);
    }

    #[Test]
    public function aTargetIsAPathOrAnHttpAddress(): void
    {
        self::assertSame('/blog/new/', (new RedirectTarget('/blog/new/'))->url);
        self::assertSame('/blog/new', (new RedirectTarget('/blog/new/?a=1'))->path());
        self::assertNull((new RedirectTarget('https://example.com/new'))->path());

        foreach (['javascript:alert(1)', '//example.com', 'ftp://example.com', 'new'] as $invalid) {
            try {
                new RedirectTarget($invalid);
                self::fail($invalid . ' was accepted.');
            } catch (\InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    #[Test]
    public function aRedirectNeverLeadsToItself(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Redirect::add(RedirectId::generate(), new RedirectSource('/old'), new RedirectTarget('/old/'), false, new \DateTimeImmutable());
    }

    #[Test]
    public function followingARedirectCountsIt(): void
    {
        $redirect = Redirect::add(RedirectId::generate(), new RedirectSource('/old'), new RedirectTarget('/new'), true, new \DateTimeImmutable('2026-01-01'));
        $redirect->follow(new \DateTimeImmutable('2026-02-01'));
        $redirect->follow(new \DateTimeImmutable('2026-03-01'));

        self::assertSame(2, $redirect->hits);
        self::assertSame('2026-03-01', $redirect->lastHitAt?->format('Y-m-d'));
    }
}
