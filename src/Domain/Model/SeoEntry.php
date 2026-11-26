<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Domain\Model;

use AlexandreBulete\DddSeoBundle\Domain\Event\SeoEntryChanged;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoEntryId;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoFields;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoTarget;
use AlexandreBulete\DddFoundation\Domain\Model\RecordsEvents;

/**
 * What an editor set by hand for one page. Exists only once something was
 * set: a page without one follows its kind's template.
 */
final class SeoEntry
{
    use RecordsEvents;

    private function __construct(
        public private(set) SeoEntryId $id,
        public private(set) SeoTarget $target,
        public private(set) ?string $title,
        public private(set) ?string $description,
        public private(set) ?string $canonical,
        public private(set) bool $noindex,
        public private(set) ?string $shareTitle,
        public private(set) ?string $shareDescription,
        public private(set) \DateTimeImmutable $updatedAt,
        public private(set) ?string $jsonLd = null,
    ) {}

    public static function set(SeoEntryId $id, SeoTarget $target, SeoFields $fields, \DateTimeImmutable $at): self
    {
        $entry = new self($id, $target, null, null, null, false, null, null, $at);
        $entry->apply($fields);
        $entry->recordEvent(new SeoEntryChanged((string) $target));

        return $entry;
    }

    public function change(SeoFields $fields, \DateTimeImmutable $at): void
    {
        if ($fields->equals($this->fields())) {
            return;
        }
        $this->apply($fields);
        $this->updatedAt = $at;
        $this->recordEvent(new SeoEntryChanged((string) $this->target));
    }

    /**
     * Records the removal; the repository deletes.
     */
    public function clear(): void
    {
        $this->recordEvent(new SeoEntryChanged((string) $this->target));
    }

    public function fields(): SeoFields
    {
        return new SeoFields($this->title, $this->description, $this->canonical, $this->noindex, $this->shareTitle, $this->shareDescription, $this->jsonLd);
    }

    private function apply(SeoFields $fields): void
    {
        $this->title = $fields->title;
        $this->description = $fields->description;
        $this->canonical = $fields->canonical;
        $this->noindex = $fields->noindex;
        $this->shareTitle = $fields->shareTitle;
        $this->shareDescription = $fields->shareDescription;
        $this->jsonLd = $fields->jsonLd;
    }
}
