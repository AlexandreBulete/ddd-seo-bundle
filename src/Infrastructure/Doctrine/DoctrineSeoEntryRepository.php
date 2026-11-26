<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Doctrine;

use AlexandreBulete\DddSeoBundle\Domain\Model\SeoEntry;
use AlexandreBulete\DddSeoBundle\Domain\Repository\SeoEntryRepositoryInterface;
use AlexandreBulete\DddSeoBundle\Domain\Service\DomainEventPublisherInterface;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoTarget;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineSeoEntryRepository implements SeoEntryRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private DomainEventPublisherInterface $eventPublisher,
    ) {}

    public function findByTarget(SeoTarget $target): ?SeoEntry
    {
        return $this->em->getRepository(SeoEntry::class)->findOneBy(['target.kind' => $target->kind, 'target.reference' => $target->reference]);
    }

    public function ofKind(string $kind): array
    {
        /** @var list<SeoEntry> $entries */
        $entries = $this->em->getRepository(SeoEntry::class)->findBy(['target.kind' => $kind]);

        return $entries;
    }

    public function excludedFromSitemap(): array
    {
        /** @var list<SeoEntry> $entries */
        $entries = $this->em->createQueryBuilder()
            ->select('e')
            ->from(SeoEntry::class, 'e')
            ->where('e.noindex = :noindex')
            ->orWhere('e.canonical IS NOT NULL')
            ->setParameter('noindex', true)
            ->getQuery()
            ->getResult();

        return $entries;
    }

    public function save(SeoEntry $entry): void
    {
        $this->em->wrapInTransaction(function () use ($entry): void {
            $this->em->persist($entry);
            $this->em->flush();
            $this->eventPublisher->publishAll($entry->releaseEvents());
        });
    }

    public function remove(SeoEntry $entry): void
    {
        $this->em->wrapInTransaction(function () use ($entry): void {
            $this->em->remove($entry);
            $this->em->flush();
            $this->eventPublisher->publishAll($entry->releaseEvents());
        });
    }
}
