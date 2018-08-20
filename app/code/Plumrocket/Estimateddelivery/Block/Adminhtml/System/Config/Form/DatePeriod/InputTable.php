<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Estimateddelivery
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Estimateddelivery\Block\Adminhtml\System\Config\Form\DatePeriod;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Plumrocket\Estimateddelivery\Block\Adminhtml\System\Config\Form\DatePeriod\InputTable\Column;

class InputTable extends Extended implements RendererInterface
{
    const HIDDEN_ELEMENT_CLASS = 'hidden-input-table';

    /**
     * @var \Magento\Framework\Data\Form\Element\AbstractElement
     */
    protected $_element;

    /**
     * @var null | string
     */
    protected $_containerFieldId = null;

    /**
     * @var null | string
     */
    protected $_rowKey = null;

    /**
     * @var \Magento\Framework\Data\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * InputTable constructor.
     *
     * @param \Magento\Backend\Block\Template\Context   $context
     * @param \Magento\Backend\Helper\Data              $backendHelper
     * @param \Magento\Framework\Data\CollectionFactory $collectionFactory
     * @param \Magento\Framework\DataObjectFactory      $dataObjectFactory
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Data\CollectionFactory $collectionFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
       $this->collectionFactory = $collectionFactory;
       $this->dataObjectFactory = $dataObjectFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
    }

    // ******************************************
    // *                                        *
    // *           Grid functions               *
    // *                                        *
    // ******************************************
    public function _construct()
    {
        parent::_construct();
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        $this->setMessageBlockVisibility(false);
    }

    /**
     * @param string                              $columnId
     * @param array|\Magento\Framework\DataObject $column
     *
     * @return $this
     * @throws \Exception
     */
    public function addColumn($columnId, $column)
    {
        if (is_array($column)) {
            $column['sortable'] = false;
            $this->getColumnSet()->setChild(
                $columnId,
                $this->getLayout()
                    ->createBlock(Column::class)
                    ->setData($column)
                    ->setId($columnId)
                    ->setGrid($this)
            );
            $this->getColumnSet()->getChildBlock($columnId)->setGrid($this);
        } else {
            throw new \Exception(__('Please correct the column format and try again.'));
        }

        $this->_lastColumnId = $columnId;
        return $this;
    }

    public function canDisplayContainer()
    {
        return false;
    }

    protected function _prepareLayout()
    {
        return \Magento\Backend\Block\Widget::_prepareLayout();
    }

    public function setArray($array)
    {
        $collection = $this->collectionFactory->create();
        $i = 1;
        foreach ($array as $item) {
            if (! $item instanceof \Magento\Framework\DataObject) {
                $item = $this->dataObjectFactory->create(['data' => $item]);
            }
            if (!$item->getId()) {
                $item->setId($i);
            }
            $collection->addItem($item);
            $i++;
        }
        $this->setCollection($collection);
        return $this;
    }

    public function getRowKey()
    {
        return $this->_rowKey;
    }

    public function setRowKey($key)
    {
        $this->_rowKey = $key;
        return $this;
    }

    public function getContainerFieldId()
    {
        return $this->_containerFieldId;
    }

    public function setContainerFieldId($name)
    {
        $this->_containerFieldId = $name;
        return $this;
    }

    /**
     * Retrieve grid html
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = parent::_toHtml();
        $html = preg_replace(
            '/(\s+class\s*=\s*["\'](?:\s*|[^"\']*\s+)messages)((?:\s*|\s+[^"\']*)["\'])/isU',
            '$1 ' . self::HIDDEN_ELEMENT_CLASS . ' $2',
            $html
        );
        $html = str_replace(
            '<div class="admin__data-grid-wrap',
            '<div id="' . $this->getHtmlId() . '_wrap" class="admin__data-grid-wrap',
            $html
        );
        $html .= $this->_getCss();
        return $html;
    }

    /**
     * Return additional styles
     *
     * @return string
     */
    protected function _getCss()
    {
        $id = '#' . $this->getHtmlId() . '_wrap';
        return "<style>
            .messages." . self::HIDDEN_ELEMENT_CLASS . " {display:none}
            $id {
                margin-bottom: 0;
                padding-bottom: 0;
                padding-top: 0;
            }
            $id td {
                padding: 1rem;
                vertical-align: middle;
            }
            $id td input.checkbox[disabled] {
                display: none;
            }
            $id tr.not-active td,
            $id tr.not-active input.input-text {
                color: #999999;
            }
        </style>";
    }

    // ******************************************
    // *                                        *
    // *           Render functions             *
    // *                                        *
    // ******************************************

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        return '
            <tr>
                <td class="label">' . $element->getLabelHtml() . '</td>
                <td class="value">' . $this->toHtml() . '</td>
            </tr>';
    }

    public function setElement(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->_element = $element;
        return $this;
    }

    public function getElement()
    {
        return $this->_element;
    }
}
