<?php

/**
 * This file is part of MetaModels/attribute_translatedurl.
 *
 * (c) 2012-2016 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage AttributeTranslatedUrl
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2016 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_translatedurl/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_metamodel_translatedurl'] = [
    'config'                          => [
        'dataContainer'               => 'General',
        'switchToEdit'                => true,
        'enableVersioning'            => false,
        'sql'                         => [
        'keys'                        => [
            'att_id,item_id,language' => 'primary',
            'att_id,language'         => 'index',
            ]
        ]
    ],
    'fields'                          => [
        'tstamp'                      => [
            'sql'                     => 'int(10) unsigned NOT NULL'
        ],
        'att_id'                      => [
            'sql'                     => 'int(10) unsigned NOT NULL'
        ],
        'item_id'                     => [
            'sql'                     => 'int(10) unsigned NOT NULL'
        ],
        'language'                    => [
            'sql'                     => 'varchar(2) NOT NULL'
        ],
        'href'                        => [
            'sql'                     => 'varchar(255) NULL'
        ],
        'title'                       => [
            'sql'                     => 'varchar(255) NULL'
        ],
    ]
];
