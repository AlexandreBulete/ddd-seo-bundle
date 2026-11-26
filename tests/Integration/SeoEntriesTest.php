<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Tests\Integration;

use AlexandreBulete\DddSeoBundle\Application\Command\SaveSeoEntry\SaveSeoEntryCommand;
use AlexandreBulete\DddSeoBundle\Application\Command\SaveSeoEntry\SaveSeoEntryHandler;
use AlexandreBulete\DddSeoBundle\Application\Query\FindSitemapExclusions\FindSitemapExclusionsHandler;
use AlexandreBulete\DddSeoBundle\Application\Query\FindSitemapExclusions\FindSitemapExclusionsQuery;
use AlexandreBulete\DddSeoBundle\Domain\Exception\UnknownSeoKindException;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoFields;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoTarget;
use AlexandreBulete\DddSeoBundle\Infrastructure\Config\SeoKinds;
use AlexandreBulete\DddSeoBundle\Infrastructure\Doctrine\DoctrineSeoEntryRepository;
use AlexandreBulete\DddSeoBundle\Infrastructure\Doctrine\DoctrineSeoSettingsRepository;
use AlexandreBulete\DddSeoBundle\Infrastructure\Doctrine\DoctrineSeoTemplateRepository;
use AlexandreBulete\DddSeoBundle\Tests\Integration\Fixture\NullEventPublisher;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class SeoEntriesTest extends TestCase
{
    private EntityManagerInterface $em;
    private DoctrineSeoEntryRepository $entries;
    private SaveSeoEntryHandler $save;

    protected function setUp(): void
    {
        $this->em = SeoDatabase::migrated();
        $this->entries = new DoctrineSeoEntryRepository($this->em, new NullEventPublisher());
        $this->save = new SaveSeoEntryHandler($this->entries, new SeoKinds(['review' => ['label' => 'Reviews', 'variables' => [], 'title' => null, 'description' => null, 'type' => 'book']], ['home' => 'Home']), new MockClock());
    }

    protected function tearDown(): void
    {
        $this->em->getConnection()->close();
    }

    #[Test]
    public function anEntryIsSetChangedThenClearedWhenEmptied(): void
    {
        $target = new SeoTarget('review', '0196f3c4-8a2b-7c3d-9e4f-5a6b7c8d9e0f');

        ($this->save)(new SaveSeoEntryCommand($target, new SeoFields('Un titre')));
        ($this->save)(new SaveSeoEntryCommand($target, new SeoFields('Un autre', noindex: true)));
        $this->em->clear();

        $entry = $this->entries->findByTarget($target);
        self::assertNotNull($entry);
        self::assertSame('Un autre', $entry->title);
        self::assertTrue($entry->noindex);
        self::assertCount(1, $this->entries->ofKind('review'));

        ($this->save)(new SaveSeoEntryCommand($target, new SeoFields()));
        $this->em->clear();
        self::assertNull($this->entries->findByTarget($target));
    }

    #[Test]
    public function noindexAndCanonicalPagesLeaveTheSitemap(): void
    {
        ($this->save)(new SaveSeoEntryCommand(new SeoTarget('review', 'a'), new SeoFields(noindex: true)));
        ($this->save)(new SaveSeoEntryCommand(new SeoTarget('route', 'home'), new SeoFields(canonical: '/elsewhere')));
        ($this->save)(new SaveSeoEntryCommand(new SeoTarget('review', 'b'), new SeoFields('Gardée')));

        $exclusions = (new FindSitemapExclusionsHandler($this->entries, new DoctrineSeoSettingsRepository($this->em, new NullEventPublisher())))(new FindSitemapExclusionsQuery());

        self::assertTrue($exclusions->indexing);
        self::assertTrue($exclusions->excludes('review', 'a'));
        self::assertTrue($exclusions->excludes('route', 'home'));
        self::assertFalse($exclusions->excludes('review', 'b'));
    }

    #[Test]
    public function anUndeclaredKindIsRefused(): void
    {
        $this->expectException(UnknownSeoKindException::class);
        ($this->save)(new SaveSeoEntryCommand(new SeoTarget('post', 'a'), new SeoFields('Un titre')));
    }

    #[Test]
    public function aTemplateIsEmptyUntilSaved(): void
    {
        $templates = new DoctrineSeoTemplateRepository($this->em, new NullEventPublisher());
        $template = $templates->get('review');
        self::assertNull($template->title);

        $template->change('{title} — {site}', null, new \DateTimeImmutable());
        $templates->save($template);
        $this->em->clear();

        self::assertSame('{title} — {site}', $templates->get('review')->title);
    }
}
