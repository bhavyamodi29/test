<?php
/**
 *
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_AutoInvoice
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AutoInvoice\Controller\Adminhtml\Rule;

use Bss\AutoInvoice\Controller\Adminhtml\Rule;
use Bss\AutoInvoice\Helper\Data;
use Bss\AutoInvoice\Api\RuleRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;

class Save extends Rule
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param RuleRepositoryInterface $ruleRepository
     * @param LoggerInterface $logger
     * @param Data $helper
     * @param Json $json
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        RuleRepositoryInterface $ruleRepository,
        LoggerInterface $logger,
        Data $helper,
        Json $json
    ) {
        $this->helper = $helper;
        $this->json = $json;
        parent::__construct($context, $coreRegistry, $resultPageFactory, $ruleRepository, $logger);
    }


    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $isPost = $this->getRequest()->getPost();

        if ($isPost) {
            $data = $this->getRequest()->getPostValue();
            $ruleId = $data['rule']['entity_id'] ?? 0;

            //validate rule
            if ($mess = $this->validateRule($data['rule']['conditions'])) {
                return $this->goBack($ruleId, $mess);
            }
            $data = $this->prepareData($data);

            $ruleModel = $this->ruleRepository->getById($ruleId);

            $ruleModel->loadPost($data['rule']);

            try {
                $ruleModel->save();
                return $this->goBack($ruleId);
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Please review the form and make corrections'));
            }
            return $resultRedirect->setPath('*/*/edit', ['id' => $ruleId]);
        }
        return $resultRedirect->setPath('*/*/*');
    }

    /**
     * Prepares specific data
     *
     * @param array $data
     * @return mixed
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function prepareData($data)
    {
        try {
            //handle convert groups array to string
            if (isset($data['rule']['store_views'])) {
                $store_filter = array_filter(array_map('trim', $data['rule']['store_views']), 'strlen');
                $data['rule']['store_views'] = implode(',', $store_filter);
            }

            // fix for before data of older version
            if (isset($data['parameters']['conditions'])) {
                $data['rule']['conditions'] = $data['rule']['conditions'] + $data['parameters']['conditions'];
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }

        return $data;
    }

    /**
     * @param int $ruleId
     * @param string $mess
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\Controller\Result\Json
     */
    protected function goBack($ruleId, $mess = '')
    {
        if ($this->getRequest()->getParam('back', false) || $mess) {
            $this->messageManager->addErrorMessage($mess);
            return $this->returnResult('*/*/edit', ['id' => $ruleId, '_current' => true], ['error' => false]);
        }
        $this->messageManager->addSuccessMessage(__('The item has been saved.'));
        // Go to grid page
        return $this->returnResult('*/*/', [], ['error' => false]);
    }

    /**
     * @param string $path
     * @param array $params
     * @param array $response
     * @return \Magento\Framework\Controller\Result\Json|\Magento\Backend\Model\View\Result\Redirect
     */
    private function returnResult($path = '', array $params = [], array $response = [])
    {
        if ($this->isAjax()) {
            $layout = $this->helper->getLayoutFactory()->create();
            $layout->initMessages();

            $response['messages'] = [$layout->getMessagesBlock()->getGroupedHtml()];
            $response['params'] = $params;
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($response);
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath($path, $params);
    }

    /**
     * @return bool
     */
    private function isAjax()
    {
        return $this->getRequest()->getParam('isAjax');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_AutoInvoice::config_autoinvoice');
    }

    /**
     * Check if rule is set
     *
     * @param array $rule
     * @return \Magento\Framework\Phrase|string
     */
    private function validateRule($rule)
    {
        $message = '';
        if (count($rule) === 1) {
            return __('Please set the rule before saving.');
        }
        $count = 1;
        foreach ($rule as $item) {
            if ((!isset($item['value']) || $item['value'] === '') && $count > 1) {
                $message =
                    __('Can not save the rule cause the attribute "%1" is not set', $item['attribute']);
                break;
            }
            $count++;
        }
        return $message;
    }
}
