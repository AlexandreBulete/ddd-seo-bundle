<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource;

use AlexandreBulete\DddSeoBundle\Domain\Model\Redirect;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\RedirectSource;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\RedirectTarget;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Grid\RedirectGrid;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\State\Processor\CreateRedirectProcessor;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\State\Processor\DeleteRedirectProcessor;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\State\Processor\UpdateRedirectProcessor;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\State\Provider\RedirectBulkItemsProvider;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\State\Provider\RedirectItemProvider;
use AlexandreBulete\DddSeoBundle\Infrastructure\Symfony\Form\Admin\RedirectType;
use AlexandreBulete\DddSeoBundle\Infrastructure\Symfony\Validator\FreeRedirectSource;
use Sylius\Resource\Metadata\AsResource;
use Sylius\Resource\Metadata\BulkDelete;
use Sylius\Resource\Metadata\Create;
use Sylius\Resource\Metadata\Delete;
use Sylius\Resource\Metadata\Index;
use Sylius\Resource\Metadata\Update;
use Sylius\Resource\Model\ResourceInterface;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[AsResource(
    alias: 'seo.redirect',
    section: 'admin',
    formType: RedirectType::class,
    templatesDir: '@SyliusAdminUi/crud',
    routePrefix: '/admin',
    name: 'redirect',
    pluralName: 'redirects',
    driver: false,
    operations: [
        new Create(processor: CreateRedirectProcessor::class, notificationMessage: 'seo.flash.redirect_added'),
        new Update(provider: RedirectItemProvider::class, processor: UpdateRedirectProcessor::class, notificationMessage: 'seo.flash.redirect_saved'),
        new Delete(provider: RedirectItemProvider::class, processor: DeleteRedirectProcessor::class, notificationMessage: 'seo.flash.redirect_removed'),
        new BulkDelete(provider: RedirectBulkItemsProvider::class, processor: DeleteRedirectProcessor::class, notificationMessage: 'seo.flash.redirects_removed'),
        new Index(grid: RedirectGrid::class),
    ],
)]
#[FreeRedirectSource]
final class RedirectResource implements ResourceInterface
{
    public function __construct(
        public ?AbstractUid $id = null,
        #[Assert\NotBlank]
        public ?string $source = null,
        #[Assert\NotBlank]
        public ?string $target = null,
        public bool $automatic = false,
        public int $hits = 0,
        public ?\DateTimeImmutable $lastHitAt = null,
    ) {}

    public static function fromModel(Redirect $redirect): self
    {
        return new self($redirect->id->value(), $redirect->source, $redirect->target, $redirect->automatic, $redirect->hits, $redirect->lastHitAt);
    }

    public function getId(): ?AbstractUid
    {
        return $this->id;
    }

    /**
     * The domain's rules, as field errors rather than a failed save.
     */
    #[Assert\Callback]
    public function validateAddresses(ExecutionContextInterface $context): void
    {
        $source = null;
        if ($this->source !== null && $this->source !== '') {
            try {
                $source = new RedirectSource($this->source);
            } catch (\InvalidArgumentException) {
                $context->buildViolation('seo.redirect.invalid_source')->atPath('source')->addViolation();
            }
        }
        if ($this->target !== null && $this->target !== '') {
            try {
                $target = new RedirectTarget($this->target);
                if ($source !== null && $target->path() === $source->path) {
                    $context->buildViolation('seo.redirect.to_itself')->atPath('target')->addViolation();
                }
            } catch (\InvalidArgumentException) {
                $context->buildViolation('seo.redirect.invalid_target')->atPath('target')->addViolation();
            }
        }
    }

    public function sourcePath(): RedirectSource
    {
        return new RedirectSource((string) $this->source);
    }

    public function targetUrl(): RedirectTarget
    {
        return new RedirectTarget((string) $this->target);
    }

    public function originLabel(): string
    {
        return $this->automatic ? 'seo.redirect.origin_automatic' : 'seo.redirect.origin_manual';
    }
}
