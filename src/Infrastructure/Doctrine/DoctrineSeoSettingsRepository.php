<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Doctrine;

use AlexandreBulete\DddSeoBundle\Domain\Model\SeoSettings;
use AlexandreBulete\DddSeoBundle\Domain\Repository\SeoSettingsRepositoryInterface;
use AlexandreBulete\DddSeoBundle\Domain\Service\DomainEventPublisherInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineSeoSettingsRepository implements SeoSettingsRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private DomainEventPublisherInterface $eventPublisher,
    ) {}

    public function get(): SeoSettings
    {
        return $this->em->find(SeoSettings::class, SeoSettings::KEY) ?? SeoSettings::defaults();
    }

    public function save(SeoSettings $settings): void
    {
        $this->em->wrapInTransaction(function () use ($settings): void {
            $this->em->persist($settings);
            $this->em->flush();
            $this->eventPublisher->publishAll($settings->releaseEvents());
        });
    }
}
