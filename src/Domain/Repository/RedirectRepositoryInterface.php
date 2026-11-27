<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Domain\Repository;

use AlexandreBulete\DddSeoBundle\Domain\Model\Redirect;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\RedirectId;

interface RedirectRepositoryInterface
{
    public function find(RedirectId $id): ?Redirect;

    public function findBySource(string $path): ?Redirect;

    /**
     * @return list<Redirect>
     */
    public function leadingTo(string $url): array;

    /**
     * By source address; those containing `$search` when given.
     *
     * @return list<Redirect>
     */
    public function all(?string $search = null): array;

    public function save(Redirect $redirect): void;

    public function remove(Redirect $redirect): void;
}
