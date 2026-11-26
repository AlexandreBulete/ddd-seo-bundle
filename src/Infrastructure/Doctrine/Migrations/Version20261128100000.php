<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Structured data: JSON-LD set by hand on a page, its kind's switch, the
 * person behind the site. Additions only: the previous version keeps working
 * on this schema.
 */
final class Version20261128100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'SEO: structured data.';
    }

    public function up(Schema $schema): void
    {
        $schema->getTable('seo_entry')->addColumn('json_ld', Types::TEXT, ['notnull' => false]);
        $schema->getTable('seo_template')->addColumn('structured_data', Types::BOOLEAN, ['default' => true]);
        $settings = $schema->getTable('seo_settings');
        $settings->addColumn('person_name', Types::STRING, ['length' => 100, 'notnull' => false]);
        $settings->addColumn('person_url', Types::STRING, ['length' => 500, 'notnull' => false]);
        $settings->addColumn('person_same_as', Types::JSON, ['notnull' => false]);
    }

    public function down(Schema $schema): void
    {
        $schema->getTable('seo_settings')->dropColumn('person_same_as');
        $schema->getTable('seo_settings')->dropColumn('person_url');
        $schema->getTable('seo_settings')->dropColumn('person_name');
        $schema->getTable('seo_template')->dropColumn('structured_data');
        $schema->getTable('seo_entry')->dropColumn('json_ld');
    }
}
