<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Domain\Model;

use AlexandreBulete\DddSeoBundle\Domain\Event\RedirectAdded;
use AlexandreBulete\DddSeoBundle\Domain\Event\RedirectChanged;
use AlexandreBulete\DddSeoBundle\Domain\Event\RedirectRemoved;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\RedirectId;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\RedirectSource;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\RedirectTarget;
use AlexandreBulete\DddFoundation\Domain\Model\RecordsEvents;

/**
 * A permanent redirect (301) from an address that no longer answers. Added
 * by hand, or on its own when a content's address changes. Counts how often
 * it is followed: one nobody follows any more can go.
 */
final class Redirect
{
    use RecordsEvents;

    private function __construct(
        public private(set) RedirectId $id,
        public private(set) string $source,
        public private(set) string $target,
        public private(set) bool $automatic,
        public private(set) int $hits,
        public private(set) ?\DateTimeImmutable $lastHitAt,
        public private(set) \DateTimeImmutable $createdAt,
        public private(set) \DateTimeImmutable $updatedAt,
    ) {}

    public static function add(RedirectId $id, RedirectSource $source, RedirectTarget $target, bool $automatic, \DateTimeImmutable $at): self
    {
        self::assertNotItself($source, $target);
        $redirect = new self($id, $source->path, $target->url, $automatic, 0, null, $at, $at);
        $redirect->recordEvent(new RedirectAdded((string) $id, $source->path));

        return $redirect;
    }

    public function change(RedirectSource $source, RedirectTarget $target, \DateTimeImmutable $at): void
    {
        self::assertNotItself($source, $target);
        if ($source->path === $this->source && $target->url === $this->target) {
            return;
        }
        $this->source = $source->path;
        $this->target = $target->url;
        $this->updatedAt = $at;
        $this->recordEvent(new RedirectChanged((string) $this->id, $this->source));
    }

    /**
     * Another redirect now leads from this one's target: follow it to the
     * end, so that a visitor is sent once, not twice.
     */
    public function shortcut(RedirectTarget $target, \DateTimeImmutable $at): void
    {
        $this->change(new RedirectSource($this->source), $target, $at);
    }

    public function follow(\DateTimeImmutable $at): void
    {
        ++$this->hits;
        $this->lastHitAt = $at;
    }

    /**
     * Records the removal; the repository deletes.
     */
    public function remove(): void
    {
        $this->recordEvent(new RedirectRemoved((string) $this->id, $this->source));
    }

    public function sourcePath(): RedirectSource
    {
        return new RedirectSource($this->source);
    }

    public function targetUrl(): RedirectTarget
    {
        return new RedirectTarget($this->target);
    }

    private static function assertNotItself(RedirectSource $source, RedirectTarget $target): void
    {
        if ($target->path() === $source->path) {
            throw new \InvalidArgumentException('A redirect cannot lead to its own address.');
        }
    }
}
