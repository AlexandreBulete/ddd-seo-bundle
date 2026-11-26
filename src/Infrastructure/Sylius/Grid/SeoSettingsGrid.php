<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Grid;

use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource\SeoSettingsResource;
use Sylius\Bundle\GridBundle\Builder\Action\UpdateAction;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\ItemActionGroup;
use Sylius\Bundle\GridBundle\Builder\Field\DateTimeField;
use Sylius\Bundle\GridBundle\Builder\Field\StringField;
use Sylius\Bundle\GridBundle\Grid\AbstractGrid;
use Sylius\Bundle\GridBundle\Grid\ResourceAwareGridInterface;
use Sylius\Component\Grid\Builder\GridBuilderInterface;

class SeoSettingsGrid extends AbstractGrid implements ResourceAwareGridInterface
{
    public static function getName(): string
    {
        return self::class;
    }

    public function buildGrid(GridBuilderInterface $gridBuilder): void
    {
        $gridBuilder
            ->setProvider(SeoSettingsGridProvider::class)
            ->addField(StringField::create('indexingLabel')->setLabel('seo.settings.indexing')->setPath('indexingLabel'))
            ->addField(StringField::create('titlePattern')->setLabel('seo.settings.title_pattern'))
            ->addField(DateTimeField::create('updatedAt')->setLabel('sylius.ui.updated_at'))
            ->addActionGroup(ItemActionGroup::create(UpdateAction::create()))
        ;
    }

    public function getResourceClass(): string
    {
        return SeoSettingsResource::class;
    }
}
