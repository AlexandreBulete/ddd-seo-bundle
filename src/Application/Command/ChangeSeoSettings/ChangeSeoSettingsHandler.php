<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Command\ChangeSeoSettings;

use AlexandreBulete\DddSeoBundle\Domain\Model\SeoSettings;
use AlexandreBulete\DddSeoBundle\Domain\Repository\SeoSettingsRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use Psr\Clock\ClockInterface;

#[AsCommandHandler]
final readonly class ChangeSeoSettingsHandler
{
    public function __construct(
        private SeoSettingsRepositoryInterface $settings,
        private ClockInterface $clock,
    ) {}

    public function __invoke(ChangeSeoSettingsCommand $command): SeoSettings
    {
        $settings = $this->settings->get();
        $settings->change($command->indexing, $command->robotsRules, $command->titlePattern, $this->clock->now());
        $settings->describePerson($command->personName, $command->personUrl, $command->personSameAs, $this->clock->now());
        $this->settings->save($settings);

        return $settings;
    }
}
