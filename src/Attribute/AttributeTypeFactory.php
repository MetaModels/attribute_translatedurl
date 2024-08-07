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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_translatedurl/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeTranslatedUrlBundle\Attribute;

use Doctrine\DBAL\Connection;
use MetaModels\Attribute\IAttributeTypeFactory;
use MetaModels\AttributeTranslatedUrlBundle\EventListener\UrlWizardHandler;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Attribute type factory for translated url attributes.
 */
class AttributeTypeFactory implements IAttributeTypeFactory
{
    /**
     * Database connection.
     *
     * @var Connection
     */
    private Connection $connection;

    /**
     * Event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $eventDispatcher;

    /**
     * The container interface.
     *
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * Construct.
     *
     * @param Connection               $connection      Database connection.
     * @param EventDispatcherInterface $eventDispatcher Event dispatcher.
     * @param ContainerInterface       $container       Container interface.
     */
    public function __construct(
        Connection $connection,
        EventDispatcherInterface $eventDispatcher,
        ContainerInterface $container
    ) {
        $this->connection      = $connection;
        $this->eventDispatcher = $eventDispatcher;
        $this->container       = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeName()
    {
        return 'translatedurl';
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeIcon()
    {
        return 'bundles/metamodelsattributetranslatedurl/url.png';
    }

    /**
     * {@inheritdoc}
     */
    public function createInstance($information, $metaModel)
    {
        $attribute = new TranslatedUrl($metaModel, $information, $this->connection, $this->eventDispatcher);

        $this->container->get(UrlWizardHandler::class)->watch($attribute);

        return $attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function isTranslatedType()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isSimpleType()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isComplexType()
    {
        return true;
    }
}
