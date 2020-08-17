<?php

/**
 * This file is part of MetaModels/attribute_translatedurl.
 *
 * (c) 2012-2020 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_alias
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_translatedurl/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\AttributeTranslatedUrlBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

/**
 * This migration changes all database columns to allow null values.
 *
 * This became necessary with the changes for https://github.com/MetaModels/core/issues/1330.
 */
class AllowNullAndIndexMigration extends AbstractMigration
{
    /**
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Create a new instance.
     *
     * @param Connection $connection The database connection.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Return the name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Allow null values in MetaModels "translated url" attribute and change index.';
    }

    /**
     * Must only run if:
     * - the column href is set to not null
     * - and index att_lang exist.
     *
     * @return bool
     */
    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->getSchemaManager();

        if (!$schemaManager->tablesExist(['tl_metamodel', 'tl_metamodel_translatedurl'])) {
            return false;
        }

        if ($this->checkColumnNotNull() || $this->checkIndexExists()) {
            return true;
        }

        return false;
    }

    /**
     * Check column and index and fix them.
     *
     * @return MigrationResult
     */
    public function run(): MigrationResult
    {
        $message = [];

        if ($this->checkColumnNotNull()) {
            $this->fixColumnToNull('tl_metamodel_translatedurl', 'href');
            $message[] = 'Adjusted column href for "translated url" to allow NULL';
        }

        if ($this->checkIndexExists()) {
            $this->fixIndexes();
            $message[] = 'Adjusted indexes for "translated url"';
        }

        return new MigrationResult(true, \implode(" ", $message));
    }

    /**
     * Check column 'href' is not null.
     *
     * @return bool
     */
    private function checkColumnNotNull(): bool
    {
        $schemaManager = $this->connection->getSchemaManager();
        $columns       = $schemaManager->listTableColumns('tl_metamodel_translatedurl');

        if (isset($columns['href']) && $columns['href']->getNotnull()) {
            return true;
        }

        return false;
    }

    /**
     * Check index exists for att_lang.
     *
     * @return bool
     */
    private function checkIndexExists()
    {
        if ($this->database->indexExists('att_lang', 'tl_metamodel_translatedurl', true)) {
            return true;
        }

        return false;
    }

    /**
     * Fix a table column to null.
     *
     * @param string $tableName  The name of the table.
     * @param string $columnName The name of the column.
     *
     * @return void
     */
    private function fixColumnToNull(string $tableName, string $columnName): void
    {
        $this->connection->query(
            sprintf('ALTER TABLE %1$s CHANGE %1$s.%2$s %1$s.%2$s varchar(255) NULL', $tableName, $columnName)
        );
    }

    /**
     * Fix indexes.
     *
     * @return void
     */
    private function fixIndexes(): void
    {
        $this->connection->query(
            'ALTER TABLE `tl_metamodel_translatedurl` DROP INDEX `att_lang`;'
        );
        $this->connection->query(
            'ALTER TABLE `tl_metamodel_translatedurl` ADD KEY `att_id_language` (`att_id`, `language`);'
        );
    }
}
