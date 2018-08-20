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
 * @package     Plumrocket Amp
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Amp\Observer\Category\Adminhtml;

use Magento\Framework\Event\ObserverInterface;

class CategoryObserver implements ObserverInterface
{
    /**
     * Virtual \Plumrocket\Amp\ImageUploader
     * @var \Magento\Catalog\Model\ImageUploader
     */
    protected $imageUploader;

    /**
     * Request
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Constructor
     * @param \Magento\Catalog\Model\ImageUploader $imageUploader
     * @param \Magento\Framework\App\Request\Http  $request
     */
    public function __construct(
        \Magento\Catalog\Model\ImageUploader $imageUploader,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->imageUploader = $imageUploader;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

    }
}
