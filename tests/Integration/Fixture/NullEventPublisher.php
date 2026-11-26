<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Tests\Integration\Fixture;

use AlexandreBulete\DddSeoBundle\Domain\Service\DomainEventPublisherInterface;

final class NullEventPublisher implements DomainEventPublisherInterface
{
    public function publishAll(iterable $events): void {}
}
