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
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;

class Delete extends Rule
{
    /**
     * @return ResponseInterface|Redirect|ResultInterface|\Magento\Framework\View\Result\Page|void
     */
    public function execute()
    {
        $rule_id = (int) $this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($rule_id) {
            $ruleModel = $this->ruleRepository->getById($rule_id);

            if (!$ruleModel->getId()) {
                $this->messageManager->addErrorMessage(__('This item no longer exists.'));
            } else {
                try {
                    $ruleModel->delete();
                    $this->messageManager->addSuccessMessage(__('The item has been deleted.'));
                    return $resultRedirect->setPath('*/*/');
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                    return $resultRedirect->setPath('*/*/edit', ['id' => $ruleModel->getId()]);
                }
            }
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_AutoInvoice::config_autoinvoice');
    }
}
