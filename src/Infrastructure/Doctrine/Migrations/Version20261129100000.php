<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20261129100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'SEO: permanent redirects.';
    }

    public function up(Schema $schema): void
    {
        $redirect = $schema->createTable('seo_redirect');
        $redirect->addColumn('id', Types::GUID);
        $redirect->addColumn('source', Types::STRING, ['length' => 500]);
        $redirect->addColumn('target', Types::STRING, ['length' => 500]);
        $redirect->addColumn('automatic', Types::BOOLEAN);
        $redirect->addColumn('hits', Types::INTEGER);
        $redirect->addColumn('last_hit_at', Types::DATETIME_IMMUTABLE, ['notnull' => false]);
        $redirect->addColumn('created_at', Types::DATETIME_IMMUTABLE);
        $redirect->addColumn('updated_at', Types::DATETIME_IMMUTABLE);
        $redirect->addPrimaryKeyConstraint(PrimaryKeyConstraint::editor()->setUnquotedColumnNames('id')->create());
        $redirect->addUniqueIndex(['source'], 'uniq_seo_redirect_source');
        $redirect->addIndex(['target'], 'idx_seo_redirect_target');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('seo_redirect');
    }
}
