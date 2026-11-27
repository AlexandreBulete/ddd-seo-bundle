<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Tests\Integration;

use AlexandreBulete\DddSeoBundle\Infrastructure\Doctrine\Migrations\Version20261127140000;
use AlexandreBulete\DddSeoBundle\Infrastructure\Doctrine\Migrations\Version20261128100000;
use AlexandreBulete\DddSeoBundle\Infrastructure\Doctrine\Migrations\Version20261129100000;
use AlexandreBulete\DddSeoBundle\Infrastructure\Doctrine\Type\RedirectIdType;
use AlexandreBulete\DddSeoBundle\Infrastructure\Doctrine\Type\SeoEntryIdType;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\ORMSetup;
use Psr\Log\NullLogger;

/**
 * A database migrated the way an application migrates it: SQLite in memory,
 * or the one given by DDD_TEST_DATABASE_URL.
 */
final class SeoDatabase
{
    public static function migrated(): EntityManagerInterface
    {
        foreach ([SeoEntryIdType::NAME => SeoEntryIdType::class, RedirectIdType::NAME => RedirectIdType::class] as $name => $class) {
            if (!Type::hasType($name)) {
                Type::addType($name, $class);
            }
        }

        $config = ORMSetup::createConfiguration(true);
        $config->setMetadataDriverImpl(new SimplifiedXmlDriver([
            dirname(__DIR__, 2) . '/src/Infrastructure/Doctrine/Mapping' => 'AlexandreBulete\DddSeoBundle\Domain',
        ]));
        $config->enableNativeLazyObjects(true);
        $config->setSchemaAssetsFilter(static fn (string $asset): bool => str_starts_with($asset, 'seo'));

        $connection = self::connect($config);
        $schemaManager = $connection->createSchemaManager();
        foreach ($schemaManager->listTableNames() as $table) {
            $schemaManager->dropTable($table);
        }

        // What `doctrine:migrations:migrate` does for a Schema API migration.
        foreach ([Version20261127140000::class, Version20261128100000::class, Version20261129100000::class] as $class) {
            $migration = new $class($connection, new NullLogger());
            $from = $schemaManager->introspectSchema();
            $to = clone $from;
            $migration->up($to);
            foreach ($connection->getDatabasePlatform()->getAlterSchemaSQL($schemaManager->createComparator()->compareSchemas($from, $to)) as $sql) {
                $connection->executeStatement($sql);
            }
        }

        return new EntityManager($connection, $config);
    }

    private static function connect(Configuration $config): Connection
    {
        $url = getenv('DDD_TEST_DATABASE_URL');
        $params = (new DsnParser([
            'pdo-sqlite' => 'pdo_sqlite',
            'postgresql' => 'pdo_pgsql',
            'mysql' => 'pdo_mysql',
        ]))->parse(is_string($url) && $url !== '' ? $url : 'pdo-sqlite:///:memory:');

        return DriverManager::getConnection($params, $config);
    }
}
