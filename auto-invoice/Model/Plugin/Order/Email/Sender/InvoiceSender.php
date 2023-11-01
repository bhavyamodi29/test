<?php
declare(strict_types=1);

/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at thisURL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_AutoInvoice
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AutoInvoice\Model\Plugin\Order\Email\Sender;

use Magento\Sales\Model\Order\Invoice;
use Bss\AutoInvoice\Helper\AutoInvoice;
use Bss\AutoInvoice\Helper\Data;

class InvoiceSender
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var AutoInvoice
     */
    private $autoInvoiceHelper;

    /**
     * InvoiceSender constructor.
     * @param Data $helper
     * @param AutoInvoice $autoInvoiceHelper
     */
    public function __construct(Data $helper, AutoInvoice $autoInvoiceHelper)
    {
        $this->helper = $helper;
        $this->autoInvoiceHelper = $autoInvoiceHelper;
    }

    /**
     * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $subject
     * @param Invoice $invoice
     * @param false $forceSyncMode
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSend(
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $subject,
        Invoice $invoice,
        $forceSyncMode = false
    ) {
        $order = $invoice->getOrder();
        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();
        $methodCode = $method->getCode();
        $invoiceClass = $this->helper->getPdfInvoiceClass();
        if ($methodCode == 'amazon_payment' && $this->helper->enableSendInvoicePdf()) {
            $fileName = 'invoice-' . $invoice->getId() . '.pdf';
            $this->autoInvoiceHelper->checkSendPdfFile($invoiceClass, $invoice, $fileName);
        }
    }
}
