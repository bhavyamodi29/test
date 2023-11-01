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
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;

class Edit extends Rule
{
    /**
     * @return Page|ResponseInterface|Redirect|ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $rule_id = $this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();

        $model = $this->ruleRepository->getById($rule_id);
        if ($rule_id) {
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This item no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
        }

        // Restore previously entered form data from session
        $data = $this->_session->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');
        $this->coreRegistry->register('partial_invoice_rule', $model);

        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Bss_AutoInvoice::partial_invoice');
        if ($rule_id) {
            $resultPage->getConfig()->getTitle()->prepend(__("Edit Rule '%1'", $model->getRuleName()));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('Add New Rule'));

        }

        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_AutoInvoice::config_autoinvoice');
    }
}
