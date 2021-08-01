<?php

/**
 * This file is part of MetaModels/attribute_translatedurl.
 *
 * (c) 2012-2021 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_translatedurl
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2021 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_translatedurl/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeTranslatedUrlBundle\Test\Attribute;

use Doctrine\DBAL\Connection;
use MetaModels\AttributeTranslatedUrlBundle\Attribute\TranslatedUrl;
use MetaModels\IMetaModel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Unit tests to test class TranslatedUrl.
 *
 * @covers \MetaModels\AttributeTranslatedUrlBundle\Attribute\TranslatedUrl
 */
class TranslatedUrlTest extends TestCase
{
    /**
     * Mock a MetaModel.
     *
     * @param string $language         The language.
     * @param string $fallbackLanguage The fallback language.
     *
     * @return IMetaModel|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockMetaModel($language, $fallbackLanguage)
    {
        $metaModel = $this->getMockForAbstractClass(IMetaModel::class);

        $metaModel
            ->expects($this->any())
            ->method('getTableName')
            ->will($this->returnValue('mm_unittest'));

        $metaModel
            ->expects($this->any())
            ->method('getActiveLanguage')
            ->will($this->returnValue($language));

        $metaModel
            ->expects($this->any())
            ->method('getFallbackLanguage')
            ->will($this->returnValue($fallbackLanguage));

        return $metaModel;
    }

    /**
     * Test that the attribute can be instantiated.
     *
     * @return void
     */
    public function testInstantiation()
    {
        $url = new TranslatedUrl(
            $this->mockMetaModel('en', 'en'),
            [],
            $this->mockConnection(),
            $this->mockDispatcher()
        );

        $this->assertInstanceOf(TranslatedUrl::class, $url);
    }

    /**
     * Mock the database connection.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|Connection
     */
    private function mockConnection()
    {
        return $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Mock event dispatcher.
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|EventDispatcherInterface
     */
    private function mockDispatcher()
    {
        return $this->getMockForAbstractClass(EventDispatcherInterface::class);
    }
}
