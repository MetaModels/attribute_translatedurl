<?php

/**
 * This file is part of MetaModels/attribute_translatedurl.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_translatedurl
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_translatedurl/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Attribute\TranslatedUrl\Helper;

use Contao\Database;

/**
 * Upgrade handler class that changes structural changes in the database.
 * This should rarely be necessary but sometimes we need it.
 */
class UpgradeHandler
{
    /**
     * The database to use.
     *
     * @var Database
     */
    private $database;

    /**
     * Create a new instance.
     *
     * @param Database $database The database instance to use.
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Perform all upgrade steps.
     *
     * @return void
     */
    public function perform()
    {
        $this->ensureIndexNameAttIdLanguage();
        $this->ensureHrefDefaultsToNull();
    }

    /**
     * Ensure that the index types are correct.
     *
     * @return void
     */
    private function ensureIndexNameAttIdLanguage()
    {
        if (!($this->database->tableExists('tl_metamodel_translatedurl'))) {
            return;
        }

        // Renamed in 2.0.0-alpha2 (due to database.sql => dca movement).
        if (!$this->database->indexExists('att_lang', 'tl_metamodel_translatedurl', true)) {
            return;
        }
        $this->database->execute(
            'ALTER TABLE `tl_metamodel_translatedurl` DROP INDEX `att_lang`;'
        );
        $this->database->execute(
            'ALTER TABLE `tl_metamodel_translatedurl` ADD KEY `att_id_language` (`att_id`, `language`);'
        );
    }

    /**
     * Ensure the default value for the href column is correct.
     *
     * @return void
     */
    private function ensureHrefDefaultsToNull()
    {
        if (!($this->database->tableExists('tl_metamodel_translatedurl'))) {
            return;
        }

        foreach ($this->database->listFields('tl_metamodel_translatedurl', true) as $field) {
            if ('href' == $field['name'] && $field['type'] != 'index') {
                // Already updated?
                if ('NOT NULL' === $field['null']) {
                    $this->database->execute(
                        'ALTER TABLE `tl_metamodel_translatedurl` CHANGE `href` `href` varchar(255) NULL;'
                    );
                    return;
                }
                // Found!
                break;
            }
        }
    }
}
