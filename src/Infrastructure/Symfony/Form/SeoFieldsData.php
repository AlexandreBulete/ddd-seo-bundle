<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Symfony\Form;

use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoFields;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * The hand-set SEO of a page, as a form edits it.
 */
final class SeoFieldsData
{
    public function __construct(
        #[Assert\Length(max: SeoFields::TITLE_MAX_LENGTH)]
        public ?string $title = null,
        #[Assert\Length(max: SeoFields::DESCRIPTION_MAX_LENGTH)]
        public ?string $description = null,
        #[Assert\Length(max: SeoFields::CANONICAL_MAX_LENGTH)]
        #[Assert\AtLeastOneOf(
            constraints: [new Assert\Regex('#^/(?!/)\S*$#'), new Assert\Url(protocols: ['http', 'https'], requireTld: true)],
            message: 'seo.canonical.invalid',
            includeInternalMessages: false,
        )]
        public ?string $canonical = null,
        public bool $noindex = false,
        #[Assert\Length(max: SeoFields::TITLE_MAX_LENGTH)]
        public ?string $shareTitle = null,
        #[Assert\Length(max: SeoFields::DESCRIPTION_MAX_LENGTH)]
        public ?string $shareDescription = null,
        #[Assert\Length(max: SeoFields::JSON_LD_MAX_LENGTH)]
        public ?string $jsonLd = null,
    ) {}

    /**
     * The domain's rule, said on the field: valid JSON, nodes with a type.
     */
    #[Assert\Callback]
    public function validateJsonLd(ExecutionContextInterface $context): void
    {
        try {
            new SeoFields(jsonLd: $this->jsonLd);
        } catch (\InvalidArgumentException) {
            $context->buildViolation('seo.json_ld.invalid')->atPath('jsonLd')->addViolation();
        }
    }

    public static function fromFields(SeoFields $fields): self
    {
        return new self($fields->title, $fields->description, $fields->canonical, $fields->noindex, $fields->shareTitle, $fields->shareDescription, $fields->jsonLd);
    }

    public function toFields(): SeoFields
    {
        return new SeoFields($this->title, $this->description, $this->canonical, $this->noindex, $this->shareTitle, $this->shareDescription, $this->jsonLd);
    }
}
