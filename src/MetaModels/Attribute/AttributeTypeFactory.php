<?php

/**
 * This file is part of MetaModels/attribute_translatedurl.
 *
 * (c) 2012-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage AttributeTranslatedUrl
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_translatedurl/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\AttributeTranslatedUrlBundle\Attribute;

use Doctrine\DBAL\Connection;
use MetaModels\Attribute\AbstractAttributeTypeFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Attribute type factory for translated url attributes.
 */
class AttributeTypeFactory extends AbstractAttributeTypeFactory
{
    /**
     * Database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Construct.
     *
     * @param Connection               $connection      Database connection.
     * @param EventDispatcherInterface $eventDispatcher Event dispatcher.
     */
    public function __construct(Connection $connection, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct();
        $this->typeName  = 'translatedurl';
        $this->typeIcon  = 'bundles/metamodelsattributetranslatedurl/url.png';
        $this->typeClass = 'MetaModels\Attribute\TranslatedUrl\TranslatedUrl';

        $this->connection      = $connection;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function createInstance($information, $metaModel)
    {
        return new TranslatedUrl($metaModel, $information, $this->connection, $this->eventDispatcher);
    }
}
