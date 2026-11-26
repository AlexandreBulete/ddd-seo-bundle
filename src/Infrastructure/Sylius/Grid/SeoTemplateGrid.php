<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Grid;

use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource\SeoTemplateResource;
use Sylius\Bundle\GridBundle\Builder\Action\UpdateAction;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\ItemActionGroup;
use Sylius\Bundle\GridBundle\Builder\Field\StringField;
use Sylius\Bundle\GridBundle\Grid\AbstractGrid;
use Sylius\Bundle\GridBundle\Grid\ResourceAwareGridInterface;
use Sylius\Component\Grid\Builder\GridBuilderInterface;

class SeoTemplateGrid extends AbstractGrid implements ResourceAwareGridInterface
{
    public static function getName(): string
    {
        return self::class;
    }

    public function buildGrid(GridBuilderInterface $gridBuilder): void
    {
        $gridBuilder
            ->setProvider(SeoTemplateGridProvider::class)
            ->addField(StringField::create('label')->setLabel('seo.template.kind'))
            ->addField(StringField::create('title')->setLabel('seo.template.title')->setPath('effectiveTitle'))
            ->addField(StringField::create('description')->setLabel('seo.template.description')->setPath('effectiveDescription'))
            ->addActionGroup(ItemActionGroup::create(UpdateAction::create()))
        ;
    }

    public function getResourceClass(): string
    {
        return SeoTemplateResource::class;
    }
}
