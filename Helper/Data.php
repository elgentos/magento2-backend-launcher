<?php
namespace Elgentos\BackendLauncher\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Backend\Model\Menu\Filter\IteratorFactory
     */
    protected $_iteratorFactory;

    /**
     * @var \Magento\Backend\Block\Menu
     */
    protected $_blockMenu;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\Config\Model\Config\Structure
     */
    protected $_configStructure;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Backend\Model\Menu\Filter\IteratorFactory $iteratorFactory
     * @param \Magento\Backend\Block\Menu $blockMenu
     * @param \Magento\Backend\Model\UrlInterface $url
     * @param \Magento\Config\Model\Config\Structure $configStructure
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\Model\Menu\Filter\IteratorFactory $iteratorFactory,
        \Magento\Backend\Block\Menu $blockMenu,
        \Magento\Backend\Model\UrlInterface $url,
        \Magento\Config\Model\Config\Structure $configStructure
    ) {
        parent::__construct($context);
        $this->_iteratorFactory = $iteratorFactory;
        $this->_blockMenu = $blockMenu;
        $this->_url = $url;
        $this->_configStructure = $configStructure;
    }

    /**
     * Get menu config model
     *
     * @return \Magento\Backend\Model\Menu
     */
    public function getMenuModel()
    {
        return $this->_blockMenu->getMenuModel();
    }

    /**
     * Get menu filter iterator
     *
     * @param \Magento\Backend\Model\Menu $menu
     * @return \Magento\Backend\Model\Menu\Filter\Iterator
     */
    protected function getMenuIterator($menu)
    {
        return $this->_iteratorFactory->create(array('iterator' => $menu->getIterator()));
    }

    /**
     * Recursively iterate through the menu model
     * @param $menu
     * @param array $result
     * @param string $fullName
     * @return array
     */
    public function getMenuArray($menu, $itemsSeparator = ' ', & $result = array(), $fullName = '')
    {
        if (! empty($fullName)) {
            $fullName .= $itemsSeparator;
        }

        foreach ($this->getMenuIterator($menu) as $menuItem) {
            /** @var $menuItem \Magento\Backend\Model\Menu\Item  */

            if ($menuItem->getUrl() !== '#') {
                // Only add meaningful entries
                $result[] = array(
                    'value' => $menuItem->getUrl(),
                    'label' => $fullName . $menuItem->getTitle()
                );
            }

            if ($menuItem->hasChildren()) {
                $this->getMenuArray($menuItem->getChildren(), $itemsSeparator, $result, $fullName . $menuItem->getTitle());
            }
        }

        return $result;
    }

    /**
     * Iterate over all config tabs, extract sections and its subsections
     * @param string $itemsSeparator
     * @param string $itemPrefix
     * @return array
     */
    public function getConfigSectionsArray($itemsSeparator = ' ', $itemPrefix = '')
    {
        $sections = array();

        foreach ($this->_configStructure->getTabs() as $tab) {
            /** @var $tab \Magento\Config\Model\Config\Structure\Element\Tab */

            foreach ($tab->getChildren() as $section) {
                /** @var $section \Magento\Config\Model\Config\Structure\Element\Section */

                // We need the label & url again for the sub sections
                $sectionLabel = $itemPrefix . $tab->getLabel() . $itemsSeparator . $section->getLabel();
                $sectionUrl = $this->_url->getUrl('adminhtml/system_config/edit', array('section' => $section->getId()));

                // First add global section to the launcher items...
                $sections[] = ['label' => $sectionLabel, 'value' => $sectionUrl];

                foreach ($section->getChildren() as $subSection) {
                    /** @var $subSection \Magento\Config\Model\Config\Structure\Element\Section */

                    // ...then add all sub sections
                    $sections[] = [
                        'label' => $sectionLabel . $itemsSeparator . $subSection->getLabel(),
                        'value' => $sectionUrl . '#' . $section->getId() . '_' . $subSection->getId() . '-link'
                    ];
                }
            }
        }

        return $sections;
    }
}