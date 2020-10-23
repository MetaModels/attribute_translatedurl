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
 * @package    MetaModels/attribute_translatedurl
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_translatedurl/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeTranslatedUrlBundle\Attribute;

use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ManipulateWidgetEvent;
use Doctrine\DBAL\Connection;
use MetaModels\Attribute\TranslatedReference;
use MetaModels\AttributeTranslatedUrlBundle\EventListener\UrlWizardHandler;
use MetaModels\IMetaModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Handle the translated url attribute.
 */
class TranslatedUrl extends TranslatedReference
{
    /**
     * Event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Instantiate an MetaModel attribute.
     *
     * Note that you should not use this directly but use the factory classes to instantiate attributes.
     *
     * @param IMetaModel               $objMetaModel    The MetaModel instance this attribute belongs to.
     *
     * @param array                    $arrData         The information array, for attribute information, refer to
     *                                                  documentation of table tl_metamodel_attribute and documentation
     *                                                  of the certain attribute classes for information what values
     *                                                  are understood.
     *
     * @param Connection               $connection      Database connection.
     *
     * @param EventDispatcherInterface $eventDispatcher Event dispatcher.
     */
    public function __construct(
        IMetaModel $objMetaModel,
        array $arrData = [],
        Connection $connection = null,
        EventDispatcherInterface $eventDispatcher = null
    ) {
        parent::__construct($objMetaModel, $arrData, $connection);

        if (null === $eventDispatcher) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Event dispatcher is missing. It has to be passed in the constructor. Fallback will be dropped.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $eventDispatcher = System::getContainer()->get('event_dispatcher');
        }
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterUrlValue($value)
    {
        return urlencode(serialize($value));
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeSettingNames()
    {
        return \array_merge(
            parent::getAttributeSettingNames(),
            [
                'no_external_link',
                'mandatory',
                'trim_title'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getValueTable()
    {
        return 'tl_metamodel_translatedurl';
    }

    /**
     * {@inheritdoc}
     */
    public function valueToWidget($value)
    {
        if ($this->get('trim_title')) {
            return $value['href'];
        } else {
            return [$value['title'], $value['href']];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function widgetToValue($value, $idValue)
    {
        if ($this->get('trim_title')) {
            return ['href' => $value];
        } else {
            return \array_combine(['title', 'href'], $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldDefinition($overrides = [])
    {
        $arrFieldDef = parent::getFieldDefinition($overrides);

        $arrFieldDef['inputType'] = 'text';
        if (!isset($arrFieldDef['eval']['tl_class'])) {
            $arrFieldDef['eval']['tl_class'] = '';
        }
        $arrFieldDef['eval']['tl_class'] .= ' wizard inline';

        if (!$this->get('trim_title')) {
            $arrFieldDef['eval']['size']      = 2;
            $arrFieldDef['eval']['multiple']  = true;
            $arrFieldDef['eval']['tl_class'] .= ' metamodelsattribute_url';
        }

        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
        $this->eventDispatcher->addListener(
            ManipulateWidgetEvent::NAME,
            [new UrlWizardHandler($this->getMetaModel(), $this->getColName()), 'getWizard']
        );

        return $arrFieldDef;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterOptions($ids, $usedOnly, &$count = null)
    {
        // not supported
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function searchForInLanguages($pattern, $languages = [])
    {
        $pattern = \str_replace(['*', '?'], ['%', '_'], $pattern);
        $builder = $this->connection->createQueryBuilder()
            ->select('t.item_id AS id')
            ->from($this->getValueTable(), 't')
            ->groupBy('t.item_id')
            ->where('(t.title LIKE :pattern OR t.href LIKE :pattern)')
            ->andWhere('t.att_id = :id')
            ->setParameter('pattern', $pattern)
            ->setParameter('id', $this->get('id'));

        if ($languages) {
            $builder
                ->andWhere('t.language IN :languages')
                ->setParameter('languages', $languages, Connection::PARAM_STR_ARRAY);
        }

        return $builder->execute()->fetchAll(\PDO::FETCH_COLUMN, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function sortIds($ids, $direction)
    {
        $ids = (array) $ids;

        if (count($ids) < 2) {
            return $ids;
        }

        if ($direction !== 'DESC') {
            $direction = 'ASC';
        }

        $statement = $this->connection->createQueryBuilder()
            ->select('_model.id')
            ->from($this->getMetaModel()->getTableName(), '_model')
            ->leftJoin(
                '_model',
                $this->getValueTable(),
                '_active',
                '_active.item_id=_model.id AND _active.att_id=:att_id AND _active.language=:active'
            )
            ->leftJoin(
                '_model',
                $this->getValueTable(),
                '_fallback',
                'active.item_id IS NULL 
                AND _fallback.item_id=_model.id 
                AND _fallback.att_id=:att_id 
                AND _fallback.language=:fallback'
            )
            ->where('_model.id IN (:ids)')
            ->orderBY('COALESCE(_active.title, _active.href, _fallback.title, _fallback.href)', $direction)
            ->addOrderBy('COALESCE(_active.href, _fallback.href)', $direction)
            ->setParameter('att_id', $this->get('id'))
            ->setParameter('active', $this->getMetaModel()->getActiveLanguage())
            ->setParameter('fallback', $this->getMetaModel()->getFallbackLanguage())
            ->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY)
            ->execute();

        return $statement->fetchAll(\PDO::FETCH_COLUMN, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function setTranslatedDataFor($values, $language)
    {
        $values = (array) $values;
        if (!$values) {
            return;
        }

        $this->unsetValueFor(\array_keys($values), $language);

        $time = \time();

        $this->connection->transactional(
            function () use ($values, $time, $language) {
                foreach ($values as $id => $value) {
                    if (!\count(\array_filter((array) $value))) {
                        continue;
                    }

                    $params = [
                        'att_id' => $this->get('id'),
                        'item_id' => $id,
                        'language' => $language,
                        'tstamp'   => $time,
                        'href'     => $value['href'],
                        'title'    => \strlen($value['title']) ? $value['title'] : null
                    ];

                    $this->connection->insert($this->getValueTable(), $params);
                }
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslatedDataFor($ids, $language)
    {
        $ids = (array) $ids;

        if (!$ids) {
            return [];
        }

        $statement = $this->connection->createQueryBuilder()
            ->select('t.item_id AS id, t.href, title')
            ->from($this->getValueTable(), 't')
            ->where('t.att_id = :att_id')
            ->andWhere('t.language = :language')
            ->andWhere('t.item_id IN (:ids)')
            ->setParameter('att_id', $this->get('id'))
            ->setParameter('language', $language)
            ->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY)
            ->execute();

        $values = [];
        while ($result = $statement->fetch(\PDO::FETCH_OBJ)) {
            $values[$result->id] = ['href' => $result->href, 'title' => $result->title];
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function unsetValueFor($ids, $language)
    {
        $ids = (array) $ids;

        if (!$ids) {
            return;
        }

        $this->connection->createQueryBuilder()
            ->delete($this->getValueTable())
            ->where($this->getValueTable() . '.att_id = :att_id')
            ->andWhere($this->getValueTable() . '.language = :language')
            ->andWhere($this->getValueTable() . '.item_id IN (:ids)')
            ->setParameter('att_id', $this->get('id'))
            ->setParameter('language', $language)
            ->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY)
            ->execute();
    }
}
