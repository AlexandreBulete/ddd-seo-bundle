<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Tests\Integration;

use AlexandreBulete\DddSeoBundle\Application\Command\AddRedirect\AddRedirectCommand;
use AlexandreBulete\DddSeoBundle\Application\Command\AddRedirect\AddRedirectHandler;
use AlexandreBulete\DddSeoBundle\Application\Command\FollowRedirect\FollowRedirectCommand;
use AlexandreBulete\DddSeoBundle\Application\Command\FollowRedirect\FollowRedirectHandler;
use AlexandreBulete\DddSeoBundle\Domain\Model\Redirect;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\RedirectSource;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\RedirectTarget;
use AlexandreBulete\DddSeoBundle\Infrastructure\Doctrine\DoctrineRedirectRepository;
use AlexandreBulete\DddSeoBundle\Tests\Integration\Fixture\NullEventPublisher;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class RedirectsTest extends TestCase
{
    private EntityManagerInterface $em;
    private DoctrineRedirectRepository $redirects;
    private AddRedirectHandler $add;
    private FollowRedirectHandler $follow;

    protected function setUp(): void
    {
        $this->em = SeoDatabase::migrated();
        $this->redirects = new DoctrineRedirectRepository($this->em, new NullEventPublisher());
        $this->add = new AddRedirectHandler($this->redirects, new MockClock());
        $this->follow = new FollowRedirectHandler($this->redirects, new MockClock());
    }

    protected function tearDown(): void
    {
        $this->em->getConnection()->close();
    }

    #[Test]
    public function aVisitorIsSentOnceToTheLatestAddress(): void
    {
        $this->move('/blog/first', '/blog/second', automatic: true);
        $this->move('/blog/second', '/blog/third', automatic: true);
        $this->em->clear();

        self::assertSame(['/blog/first' => '/blog/third', '/blog/second' => '/blog/third'], $this->map());
        self::assertSame('/blog/third', ($this->follow)(new FollowRedirectCommand('/blog/first/')));
        self::assertNull(($this->follow)(new FollowRedirectCommand('/blog/third')));
        $this->em->clear();
        self::assertSame(1, $this->redirects->findBySource('/blog/first')?->hits);
    }

    #[Test]
    public function goingBackToAnOldAddressDropsItsRedirect(): void
    {
        $this->move('/blog/first', '/blog/second', automatic: true);
        $this->move('/blog/second', '/blog/first', automatic: true);
        $this->em->clear();

        self::assertSame(['/blog/second' => '/blog/first'], $this->map());
    }

    #[Test]
    public function anAddressAlreadyRedirectedIsRetargeted(): void
    {
        $this->move('/old', '/new');
        $this->move('/old', 'https://example.com/elsewhere');
        $this->em->clear();

        self::assertSame(['/old' => 'https://example.com/elsewhere'], $this->map());
        self::assertSame([], $this->redirects->all('nothing'));
        self::assertCount(1, $this->redirects->all('EXAMPLE'));
    }

    private function move(string $from, string $to, bool $automatic = false): void
    {
        ($this->add)(new AddRedirectCommand(new RedirectSource($from), new RedirectTarget($to), $automatic));
    }

    /**
     * @return array<string, string>
     */
    private function map(): array
    {
        return array_column(array_map(static fn (Redirect $r): array => [$r->source, $r->target], $this->redirects->all()), 1, 0);
    }
}
