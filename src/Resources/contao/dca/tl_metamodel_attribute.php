<?php

/**
 * This file is part of MetaModels/attribute_translatedurl.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_translatedurl
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_translatedurl/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['metapalettes']['translatedurl extends _simpleattribute_'] = [
    '+display' => ['trim_title']
];

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['trim_title'] = [
    'label'       => 'trim_title.label',
    'description' => 'trim_title.description',
    'exclude'     => true,
    'inputType'   => 'checkbox',
    'sql'         => 'char(1) NOT NULL default \'\'',
    'eval'        => ['tl_class' => 'clr']
];

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['dca_config']['data_provider']['tl_metamodel_translatedurl'] = [
    'source' => 'tl_metamodel_translatedurl'
];

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['dca_config']['childCondition'][] = [
    'from'   => 'tl_metamodel_attribute',
    'to'     => 'tl_metamodel_translatedurl',
    'setOn'  => [
        [
            'to_field'   => 'att_id',
            'from_field' => 'id',
        ],
    ],
    'filter' => [
        [
            'local'     => 'att_id',
            'remote'    => 'id',
            'operation' => '=',
        ],
    ]
];
