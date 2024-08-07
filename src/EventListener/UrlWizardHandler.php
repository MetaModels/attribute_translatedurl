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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_translatedurl/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeTranslatedUrlBundle\EventListener;

use Contao\CoreBundle\Picker\PickerBuilderInterface;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoBackendViewTemplate;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ManipulateWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use MetaModels\AttributeTranslatedUrlBundle\Attribute\TranslatedUrl;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This class adds the file picker wizard to the file picker widgets if necessary.
 */
class UrlWizardHandler
{
    /**
     * The name of the attribute of the MetaModel this handler should react on.
     *
     * @var array<string, array<string, TranslatedUrl>>
     */
    private array $propertyNames = [];

    /**
     * Create a new instance.
     */
    public function __construct(
        private readonly PickerBuilderInterface $pickerBuilder
    ) {
    }

    /**
     * Register an attribute
     *
     * @param TranslatedUrl $attribute The attribute.
     *
     * @return void
     */
    public function watch(TranslatedUrl $attribute)
    {
        $this->propertyNames[$attribute->getMetaModel()->getTableName()][$attribute->getColName()] = $attribute;
    }

    /**
     * Build the wizard string.
     *
     * @param ManipulateWidgetEvent $event The event.
     *
     * @return void
     */
    public function __invoke(ManipulateWidgetEvent $event)
    {
        $tableName = $event->getModel()->getProviderName();
        $propName  = $event->getProperty()->getName();

        if (!isset($this->propertyNames[$tableName][$propName])) {
            return;
        }

        $attribute = $this->propertyNames[$tableName][$propName];

        $environment = $event->getEnvironment();
        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        $inputId    = $propName . (!$attribute->get('trim_title') ? '_1' : '');
        $translator = $environment->getTranslator();
        assert($translator instanceof TranslatorInterface);

        $this->addStylesheet('metamodelsattribute_url', 'bundles/metamodelsattributeurl/style.css');

        $dispatcher = $event->getEnvironment()->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);

        $pickerUrl = $this->pickerBuilder->getUrl('cca_link');
        $urlEvent = new GenerateHtmlEvent(
            'pickpage.svg',
            $translator->translate('pagePicker', 'dc-general'),
            'style="vertical-align:text-bottom;cursor:pointer;width:20px;height:20px;"'
        );

        $dispatcher->dispatch($urlEvent, ContaoEvents::IMAGE_GET_HTML);

        $template = new ContaoBackendViewTemplate('dc_general_wizard_link_url_picker');
        $template
            ->set('name', $event->getWidget()->name)
            ->set('popupUrl', $pickerUrl)
            ->set('html', ' ' . (string) $urlEvent->getHtml())
            ->set('label', $translator->translate($event->getProperty()->getLabel(), $dataDefinition->getName()))
            ->set('id', $inputId);

        $event->getWidget()->wizard = $template->parse();
    }

    /**
     * Add the stylesheet to the backend.
     *
     * @param string $name Name The name-key of the file.
     * @param string $file File The filepath on the filesystem.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     *
     * @return void
     */
    protected function addStylesheet($name, $file)
    {
        $GLOBALS['TL_CSS'][$name] = $file;
    }
}
