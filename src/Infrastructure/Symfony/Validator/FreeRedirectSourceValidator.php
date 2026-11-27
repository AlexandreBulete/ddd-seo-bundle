<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Symfony\Validator;

use AlexandreBulete\DddSeoBundle\Domain\Repository\RedirectRepositoryInterface;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\RedirectSource;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource\RedirectResource;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class FreeRedirectSourceValidator extends ConstraintValidator
{
    public function __construct(
        private readonly RedirectRepositoryInterface $redirects,
    ) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof FreeRedirectSource) {
            throw new UnexpectedTypeException($constraint, FreeRedirectSource::class);
        }
        if (!$value instanceof RedirectResource || $value->source === null || $value->source === '') {
            return;
        }
        try {
            $source = new RedirectSource($value->source);
        } catch (\InvalidArgumentException) {
            return;
        }
        $other = $this->redirects->findBySource($source->path);
        if ($other === null || ($value->id !== null && $other->id->value()->equals($value->id))) {
            return;
        }
        $this->context->buildViolation($constraint->message)
            ->setParameter('%target%', $other->target)
            ->atPath('source')
            ->addViolation();
    }
}
