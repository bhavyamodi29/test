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
 * @category   BSS
 * @package    Bss_AutoInvoice
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AutoInvoice\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\LayoutFactory;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ENABLE_MODULE = 'autoinvoice/settings/active';
    const PAYMENT_METHODS_AVAILABLE = 'autoinvoice/settings/payment_methods';
    const ENABLE_AUTO_INVOICE = 'autoinvoice/settings/invoice';
    const CAPTURE_STATUS_FOR_ONLINE_PAYMENT_METHODS = 'autoinvoice/settings/capture';
    const PDF_ATTACHED_INVOICE_FILE_EMAIL = 'autoinvoice/settings/pdf_invoice';
    const ENABLE_AUTO_SHIPMENT = 'autoinvoice/settings/shipment';
    const ENABLE_PARTIAL_AUTO_INVOICE = 'autoinvoice/settings/partial_invoice';
    const PDF_ATTACHED_SHIPMENT_FILE_EMAIL = 'autoinvoice/settings/pdf_shipment';

    /**
     * ProductMetadataInterface
     *
     * @var \Magento\Framework\App\ProductMetadataInterface $productMetadata
     */
    protected $productMetadata;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $transaction;

    /**
     * @var Order\Pdf\Invoice
     */
    protected $pdfInvoice;

    /**
     * @var Order\Pdf\Shipment
     */
    protected $pdfShipment;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * Data constructor.
     * @param Context $context
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\DB\TransactionFactory $transaction
     * @param Order\Pdf\Invoice $pdfInvoice
     * @param Order\Pdf\Shipment $pdfShipment
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param DateTime $date
     * @param LayoutFactory $layoutFactory
     * @param CollectionFactory $productCollectionFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\DB\TransactionFactory $transaction,
        \Magento\Sales\Model\Order\Pdf\Invoice $pdfInvoice,
        \Magento\Sales\Model\Order\Pdf\Shipment $pdfShipment,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        DateTime $date,
        LayoutFactory $layoutFactory,
        CollectionFactory $productCollectionFactory
    ) {
        $this->productMetadata = $productMetadata;
        $this->transaction = $transaction;
        $this->pdfInvoice = $pdfInvoice;
        $this->pdfShipment = $pdfShipment;
        $this->transportBuilder = $transportBuilder;
        $this->date = $date;
        $this->layoutFactory = $layoutFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        parent::__construct($context);
    }

    /**
     * @return Order\Pdf\Invoice
     */
    public function getPdfInvoiceClass()
    {
        return $this->pdfInvoice;
    }

    /**
     * @return Order\Pdf\Shipment
     */
    public function getPdfShipmentClass()
    {
        return $this->pdfShipment;
    }

    /**
     * @return \Magento\Framework\Mail\Template\TransportBuilder
     */
    public function getTransportBuilder()
    {
        return $this->transportBuilder;
    }

    /**
     * @return \Magento\Framework\DB\TransactionFactory
     */
    public function returnTransaction()
    {
        return $this->transaction;
    }

    /**
     * @return bool
     */
    public function enableSendInvoicePdf()
    {
        $pdfInvoiceEnable =  $this->scopeConfig->isSetFlag(
            self::PDF_ATTACHED_INVOICE_FILE_EMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($this->isConfigActive() && $this->isConfiginvoice() && $pdfInvoiceEnable) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function enableSendShipmentPdf()
    {
        $pdfShipmentEnable =  $this->scopeConfig->isSetFlag(
            self::PDF_ATTACHED_SHIPMENT_FILE_EMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($this->isConfigActive() && $this->enabledShipment() && $pdfShipmentEnable) {
            return true;
        }
        return false;
    }

    /**
     * Check Enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        if ($this->isConfigActive() && ($this->isConfiginvoice() || $this->enabledShipment())) {
            return true;
        }
        return false;
    }

    /**
     * Check Payment methods
     *
     * @param $payment
     * @return bool
     */
    public function checkPaymentmethod($payment)
    {
        if (in_array($payment, explode(',', $this->getPaymentMethods()))) {
            return true;
        }
        return false;
    }

    /**
     * Is enabled auto create shipment
     *
     * @return bool
     */
    public function enabledShipment()
    {
        return $this->scopeConfig->isSetFlag(
            self::ENABLE_AUTO_SHIPMENT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Payment Methods
     *
     * @return string
     */
    public function getPaymentMethods()
    {
        return $this->scopeConfig->getValue(
            self::PAYMENT_METHODS_AVAILABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) ?? '';
    }

    /**
     * Get Config active
     *
     * @return bool
     */
    public function isConfigActive()
    {
        return $this->scopeConfig->isSetFlag(
            self::ENABLE_MODULE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Config invoice
     *
     * @return bool
     */
    public function isConfiginvoice()
    {
        return $this->scopeConfig->isSetFlag(
            self::ENABLE_AUTO_INVOICE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Config select capture
     *
     * @return string
     */
    public function getSelectCapture()
    {
        return $this->scopeConfig->getValue(
            self::CAPTURE_STATUS_FOR_ONLINE_PAYMENT_METHODS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Config Enable Partial Invoice
     *
     * @return string
     */
    public function isEnablePartial()
    {
        return $this->scopeConfig->getValue(
            self::ENABLE_PARTIAL_AUTO_INVOICE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) && $this->isEnabled();
    }

    /**
     * Get Magento Version
     *
     * @return bool
     */
    public function checkMagentoVersion()
    {
        $dataVersion = $this->productMetadata->getVersion();
        if (version_compare($dataVersion, '2.2.0') >= 0) {
            return false;
        }
        return true;
    }

    /**
     * @return LoggerInterface
     */
    public function returnLogger()
    {
        return $this->_logger;
    }

    /**
     * @param Order $order
     * @return bool
     */
    public function checkStateOrder($order)
    {
        return ($order->getState() == Order::STATE_NEW || $order->getState() == Order::STATE_PROCESSING);
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return LayoutFactory
     */
    public function getLayoutFactory()
    {
        return $this->layoutFactory;
    }

    /**
     * @return CollectionFactory
     */
    public function getProductCollectionFactory()
    {
        return $this->productCollectionFactory;
    }
}
