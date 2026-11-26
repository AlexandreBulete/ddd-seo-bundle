<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Command\SaveSeoEntry;

use AlexandreBulete\DddSeoBundle\Domain\Exception\UnknownSeoKindException;
use AlexandreBulete\DddSeoBundle\Domain\Model\SeoEntry;
use AlexandreBulete\DddSeoBundle\Domain\Repository\SeoEntryRepositoryInterface;
use AlexandreBulete\DddSeoBundle\Domain\Service\SeoKindCatalogInterface;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoEntryId;
use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use Psr\Clock\ClockInterface;

#[AsCommandHandler]
final readonly class SaveSeoEntryHandler
{
    public function __construct(
        private SeoEntryRepositoryInterface $entries,
        private SeoKindCatalogInterface $kinds,
        private ClockInterface $clock,
    ) {}

    public function __invoke(SaveSeoEntryCommand $command): void
    {
        if (!$this->kinds->has($command->target->kind)) {
            throw new UnknownSeoKindException($command->target->kind);
        }

        $entry = $this->entries->findByTarget($command->target);
        if ($command->fields->isEmpty()) {
            if ($entry !== null) {
                $entry->clear();
                $this->entries->remove($entry);
            }

            return;
        }

        if ($entry === null) {
            $entry = SeoEntry::set(SeoEntryId::generate(), $command->target, $command->fields, $this->clock->now());
        } else {
            $entry->change($command->fields, $this->clock->now());
        }
        $this->entries->save($entry);
    }
}
