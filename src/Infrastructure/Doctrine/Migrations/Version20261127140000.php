<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Doctrine\Migrations;

use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Creates the hand-set SEO of pages, the templates by kind and the settings.
 *
 * DBAL's built-in types only, never the bundle's own: a migration is a
 * snapshot, and must not change when a mapped type changes tomorrow. The
 * integration tests check that the result is the mapped schema on
 * PostgreSQL, MySQL and SQLite.
 */
final class Version20261127140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'SEO: entries, templates and settings.';
    }

    public function up(Schema $schema): void
    {
        $entry = $schema->createTable('seo_entry');
        $entry->addColumn('id', Types::GUID);
        $entry->addColumn('target_kind', Types::STRING, ['length' => 30]);
        $entry->addColumn('target_reference', Types::STRING, ['length' => 150]);
        $entry->addColumn('title', Types::STRING, ['length' => 120, 'notnull' => false]);
        $entry->addColumn('description', Types::STRING, ['length' => 320, 'notnull' => false]);
        $entry->addColumn('canonical', Types::STRING, ['length' => 500, 'notnull' => false]);
        $entry->addColumn('noindex', Types::BOOLEAN);
        $entry->addColumn('share_title', Types::STRING, ['length' => 120, 'notnull' => false]);
        $entry->addColumn('share_description', Types::STRING, ['length' => 320, 'notnull' => false]);
        $entry->addColumn('updated_at', Types::DATETIME_IMMUTABLE);
        $entry->addPrimaryKeyConstraint(PrimaryKeyConstraint::editor()->setUnquotedColumnNames('id')->create());
        $entry->addUniqueIndex(['target_kind', 'target_reference'], 'uniq_seo_entry_target');

        $template = $schema->createTable('seo_template');
        $template->addColumn('kind', Types::STRING, ['length' => 30]);
        $template->addColumn('title', Types::STRING, ['length' => 200, 'notnull' => false]);
        $template->addColumn('description', Types::STRING, ['length' => 200, 'notnull' => false]);
        $template->addColumn('updated_at', Types::DATETIME_IMMUTABLE, ['notnull' => false]);
        $template->addPrimaryKeyConstraint(PrimaryKeyConstraint::editor()->setUnquotedColumnNames('kind')->create());

        $settings = $schema->createTable('seo_settings');
        $settings->addColumn('settings_key', Types::STRING, ['length' => 30]);
        $settings->addColumn('indexing', Types::BOOLEAN);
        $settings->addColumn('robots_rules', Types::TEXT);
        $settings->addColumn('title_pattern', Types::STRING, ['length' => 200]);
        $settings->addColumn('updated_at', Types::DATETIME_IMMUTABLE, ['notnull' => false]);
        $settings->addPrimaryKeyConstraint(PrimaryKeyConstraint::editor()->setUnquotedColumnNames('settings_key')->create());
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('seo_settings');
        $schema->dropTable('seo_template');
        $schema->dropTable('seo_entry');
    }
}
