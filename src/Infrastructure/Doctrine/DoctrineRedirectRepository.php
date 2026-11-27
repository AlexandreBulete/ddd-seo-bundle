<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Doctrine;

use AlexandreBulete\DddSeoBundle\Domain\Model\Redirect;
use AlexandreBulete\DddSeoBundle\Domain\Repository\RedirectRepositoryInterface;
use AlexandreBulete\DddSeoBundle\Domain\Service\DomainEventPublisherInterface;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\RedirectId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineRedirectRepository implements RedirectRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private DomainEventPublisherInterface $eventPublisher,
    ) {}

    public function find(RedirectId $id): ?Redirect
    {
        return $this->em->find(Redirect::class, $id->value());
    }

    public function findBySource(string $path): ?Redirect
    {
        return $this->em->getRepository(Redirect::class)->findOneBy(['source' => $path]);
    }

    public function leadingTo(string $url): array
    {
        /** @var list<Redirect> $redirects */
        $redirects = $this->em->getRepository(Redirect::class)->findBy(['target' => $url]);

        return $redirects;
    }

    public function all(?string $search = null): array
    {
        $query = $this->em->createQueryBuilder()
            ->select('r')
            ->from(Redirect::class, 'r')
            ->orderBy('r.source', 'ASC');
        $search = trim((string) $search);
        if ($search !== '') {
            $query->where('LOWER(r.source) LIKE :search OR LOWER(r.target) LIKE :search')
                ->setParameter('search', '%' . addcslashes(mb_strtolower($search), '%_\\') . '%');
        }
        /** @var list<Redirect> $redirects */
        $redirects = $query->getQuery()->getResult();

        return $redirects;
    }

    public function save(Redirect $redirect): void
    {
        $this->em->wrapInTransaction(function () use ($redirect): void {
            $this->em->persist($redirect);
            $this->em->flush();
            $this->eventPublisher->publishAll($redirect->releaseEvents());
        });
    }

    public function remove(Redirect $redirect): void
    {
        $this->em->wrapInTransaction(function () use ($redirect): void {
            $this->em->remove($redirect);
            $this->em->flush();
            $this->eventPublisher->publishAll($redirect->releaseEvents());
        });
    }
}
