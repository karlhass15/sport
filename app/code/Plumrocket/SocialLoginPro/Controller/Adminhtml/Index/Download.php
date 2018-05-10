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
 * @package     Plumrocket_SocialLoginPro
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\SocialLoginPro\Controller\Adminhtml\Index;

class Download extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $dir;

    /**
     * Download constructor.
     *
     * @param \Magento\Framework\App\Action\Context            $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Controller\Result\RawFactory  $resultRawFactory
     * @param \Magento\Framework\Filesystem\DirectoryList      $dir
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Filesystem\DirectoryList $dir
    ) {
        $this->fileFactory      = $fileFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->dir              = $dir;
        parent::__construct($context);
    }

    public function execute ()
    {
        $fileName = \Plumrocket\SocialLoginPro\Model\Account::LOG_FILE;
        $absolutePath = $this->dir->getPath($this->dir::ROOT);
        $logPath = $this->dir->getPath($this->dir::LOG) . DIRECTORY_SEPARATOR . $fileName;
        if (file_exists($logPath) && file_get_contents($logPath) !== '') {
            $logPath = str_replace($absolutePath, '', $this->dir->getPath($this->dir::LOG) . DIRECTORY_SEPARATOR . $fileName);
            $content = [
                'type' => 'filename',
                'value' => $logPath,
                'rm' => false
            ];
            $this->fileFactory->create($fileName, $content);
            $resultRaw = $this->resultRawFactory->create();
            return $resultRaw;
        } else {
            $this->messageManager->addError( __('The log file is missing.') );
            $resultRedirect = $this->resultFactory->create($this->resultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
    }
}
