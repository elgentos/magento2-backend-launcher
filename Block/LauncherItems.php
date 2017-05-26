<?php
namespace Elgentos\BackendLauncher\Block;

class LauncherItems extends \Magento\Backend\Block\Template
{
    const ITEMS_SEPARATOR = ' - ';

    const CONFIG_ITEMS_PREFIX = 'Configuration - ';

    const FIRST_SEQUENCE_KEY_CONFIG_PATH = 'elgentos_backendlauncher/keyboard/sequence_a';

    const SECOND_SEQUENCE_KEY_CONFIG_PATH = 'elgentos_backendlauncher/keyboard/sequence_b';

    protected $_template = 'launcher.phtml';

    /**
     * @var \Elgentos\BackendLauncher\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_url;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Elgentos\BackendLauncher\Helper\Data $dataHelper
     * @param \Magento\Backend\Model\UrlInterface $url
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Elgentos\BackendLauncher\Helper\Data $dataHelper,
        \Magento\Backend\Model\UrlInterface $url,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_dataHelper = $dataHelper;
        $this->_url = $url;
    }

    /**
     * Transform $this->getMenuArray() into a JSON array to be fed to ui.autocomplete
     * @return string
     */
    public function getItemsJson()
    {
        $menuArray = $this->_dataHelper->getMenuArray($this->_dataHelper->getMenuModel(), self::ITEMS_SEPARATOR);
        $configSectionsArray = $this->_dataHelper->getConfigSectionsArray(self::ITEMS_SEPARATOR, self::CONFIG_ITEMS_PREFIX);

        return json_encode(array_merge($menuArray, $configSectionsArray), JSON_UNESCAPED_SLASHES);
    }

    /**
     * Gets the first sequence key from the configuration storage
     * @return string
     */
    public function getFirstSequenceKey()
    {
        return (string) $this->_scopeConfig->getValue(self::FIRST_SEQUENCE_KEY_CONFIG_PATH);
    }

    /**
     * * Gets the first sequence key from the configuration storage
     * @return string
     */
    public function getSecondSequenceKey()
    {
        return (string) $this->_scopeConfig->getValue(self::SECOND_SEQUENCE_KEY_CONFIG_PATH);
    }

    /**
     * Processing block html after rendering
     * @todo this is duplicate code
     * @see Magento\Backend\Block\Menu::_afterToHtml()
     *
     * @param   string $html
     * @return  string
     */
    public function _afterToHtml($html)
    {
        $html = preg_replace_callback(
            '#' . \Magento\Backend\Model\UrlInterface::SECRET_KEY_PARAM_NAME . '/\$([^\/].*)/([^\/].*)/([^\$].*)\$#U',
            array($this, '_callbackSecretKey'),
            $html
        );

        return $html;
    }

    /**
     * Replace Callback Secret Key
     * @todo this is duplicate code
     * @see Magento\Backend\Block\Menu::_callbackSecretKey
     *
     * @param string[] $match
     * @return string
     */
    protected function _callbackSecretKey($match)
    {
        return \Magento\Backend\Model\UrlInterface::SECRET_KEY_PARAM_NAME . '/' . $this->_url->getSecretKey(
            $match[1],
            $match[2],
            $match[3]
        );
    }
}