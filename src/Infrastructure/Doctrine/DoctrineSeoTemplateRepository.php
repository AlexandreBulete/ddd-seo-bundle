<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Doctrine;

use AlexandreBulete\DddSeoBundle\Domain\Model\SeoTemplate;
use AlexandreBulete\DddSeoBundle\Domain\Repository\SeoTemplateRepositoryInterface;
use AlexandreBulete\DddSeoBundle\Domain\Service\DomainEventPublisherInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineSeoTemplateRepository implements SeoTemplateRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private DomainEventPublisherInterface $eventPublisher,
    ) {}

    public function get(string $kind): SeoTemplate
    {
        return $this->em->find(SeoTemplate::class, $kind) ?? SeoTemplate::defaults($kind);
    }

    public function save(SeoTemplate $template): void
    {
        $this->em->wrapInTransaction(function () use ($template): void {
            $this->em->persist($template);
            $this->em->flush();
            $this->eventPublisher->publishAll($template->releaseEvents());
        });
    }
}
