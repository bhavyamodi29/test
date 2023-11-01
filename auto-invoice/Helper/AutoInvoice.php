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
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AutoInvoice\Helper;

use Bss\AutoInvoice\Model\KlarnaRepository;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\ShipmentDocumentFactory;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Model\Service\InvoiceService;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AutoInvoice extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Bss\AutoInvoice\Model\ShippingOrder
     */
    protected $shippingOrder;

    /**
     * @var Data
     */
    private $helper;

    /**
     * InvoiceSender
     *
     * @var InvoiceSender $invoiceSender
     */
    private $invoiceSender;

    /**
     * ShipmentSender
     *
     * @var ShipmentSender $shipmentSender
     */
    private $shipmentSender;

    /**
     * ShipmentFactory
     *
     * @var ShipmentFactory $shipmentFactory
     */
    private $shipmentFactory;

    /**
     * ShipmentDocumentFactory
     *
     * @var ShipmentDocumentFactory $shipmentDocumentFactory
     */
    private $shipmentDocumentFactory;

    /**
     * InvoiceService
     *
     * @var InvoiceService $invoiceService
     */
    private $invoiceService;

    /**
     * @var \Magento\Sales\Model\Convert\Order
     */
    private $orderConverter;

    /**
     * @var MultiSourceInventory
     */
    protected $multiSourceInventory;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var KlarnaRepository
     */
    protected $klarnaRepository;

    /**
     * @var Partial
     */
    protected $partialHelper;

    /**
     * AutoInvoice constructor.
     *
     * @param \Bss\AutoInvoice\Model\ShippingOrder $shippingOrder
     * @param Context $context
     * @param InvoiceSender $invoiceSender
     * @param ShipmentSender $shipmentSender
     * @param ShipmentDocumentFactory $shipmentDocumentFactory
     * @param ShipmentFactory $shipmentFactory
     * @param InvoiceService $invoiceService
     * @param Data $helper
     * @param \Magento\Sales\Model\Convert\Order $orderConverter
     * @param MultiSourceInventory $multiSourceInventory
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param KlarnaRepository $klarnaRepository
     * @param Partial $partialHelper
     */
    public function __construct(
        \Bss\AutoInvoice\Model\ShippingOrder            $shippingOrder,
        Context                                         $context,
        InvoiceSender                                   $invoiceSender,
        ShipmentSender                                  $shipmentSender,
        ShipmentDocumentFactory                         $shipmentDocumentFactory,
        ShipmentFactory                                 $shipmentFactory,
        InvoiceService                                  $invoiceService,
        Data                                            $helper,
        \Magento\Sales\Model\Convert\Order              $orderConverter,
        MultiSourceInventory                            $multiSourceInventory,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Sales\Model\OrderFactory               $orderFactory,
        KlarnaRepository                                $klarnaRepository,
        Partial                                         $partialHelper
    ) {
        $this->shippingOrder = $shippingOrder;
        parent::__construct($context);
        $this->helper = $helper;
        $this->invoiceSender = $invoiceSender;
        $this->shipmentSender = $shipmentSender;
        $this->invoiceService = $invoiceService;
        $this->shipmentFactory = $shipmentFactory;
        $this->shipmentDocumentFactory = $shipmentDocumentFactory;
        $this->orderConverter = $orderConverter;
        $this->multiSourceInventory = $multiSourceInventory;
        $this->productMetadata = $productMetadata;
        $this->orderFactory = $orderFactory;
        $this->klarnaRepository = $klarnaRepository;
        $this->partialHelper = $partialHelper;
    }

    /**
     * @param Order $order
     * @return \Magento\Framework\DB\Transaction|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createInvoice($order)
    {
        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();
        $methodCode = $method->getCode();
        $selectCapture = $this->helper->getSelectCapture();

        if ($methodCode === "amazon_payment" && $selectCapture === "online") {
            $orderIncrementId = $order->getIncrementId();
            $order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);
        }
        if ($this->checkKlarnaPayment($methodCode, $selectCapture)) {
            $orderId = $order->getId();
            $klarnaId = $this->_getRequest()->getParam('id');
            $this->klarnaRepository->saveKlarnaOrder($orderId, $klarnaId);
        }
        $orderItemsQtyToInvoice = [];
        if ($this->helper->isEnablePartial()) {
            foreach ($order->getAllItems() as $item) {
                if ($this->partialHelper->validateProduct($item->getProduct())) {
                    if ($item->getParentItemId()) {
                        $orderItemsQtyToInvoice[$item->getParentItemId()] = $item->getParentItem()->getQtyOrdered();
                    }
                    $orderItemsQtyToInvoice[$item->getId()] = $item->getQtyOrdered();
                }
            }
            if (count($orderItemsQtyToInvoice) === 0) {
                return;
            }
        }
        /** @var Invoice $invoice */
        $invoice = $this->invoiceService->prepareInvoice($order, $orderItemsQtyToInvoice);
        $this->checkInvoice($invoice);
        if ($order->getPayment()->getMethodInstance()->isOffline()) {
            $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
        } else {
            $invoice->setRequestedCaptureCase($selectCapture);
        }
        $transactionSave = false;

        try {
            if ($this->checkKlarnaPayment($methodCode, $selectCapture)) {
                $invoice->setIsPaid(false);
            }
            $invoice->register();
            $invoice->getOrder()->setCustomerNoteNotify(false);
            $invoice->getOrder()->setIsInProcess(true);
            $invoice->save();
            if ($methodCode == 'reddot_rapi') {
                $invoice->pay()->save();
            }
            $transactionSave = $this->helper->returnTransaction()->create()->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            );
            $transactionSave->save();
            $order->addStatusHistoryComment(
                __('Automatically Invoiced by BSS AutoInvoice.'),
                false
            )->save();
            //check send pdf invoice in email
            if ($this->helper->enableSendInvoicePdf() && $methodCode !== 'amazon_payment') {
                $invoiceClass = $this->helper->getPdfInvoiceClass();
                $fileName = 'invoice-' . $invoice->getId() . '.pdf';
                $this->checkSendPdfFile($invoiceClass, $invoice, $fileName);
            }
            //send Email
            $this->invoiceSender->send($invoice);
        } catch (\Exception $e) {
            $this->helper->returnLogger()->error($e->getMessage());
        }
        return $transactionSave;
    }

    /**
     * @param mixed $invoice
     */
    public function checkSendPdfFile($pdfClass, $file, $fileName)
    {
        $pdf = $pdfClass->getPdf([$file]);
        if ($pdf->render()) {
            $this->helper->getTransportBuilder()->addAttachment(
                $pdf->render(),
                \Laminas\Mime\Mime::TYPE_OCTETSTREAM,
                \Laminas\Mime\Mime::DISPOSITION_ATTACHMENT,
                \Laminas\Mime\Mime::ENCODING_BASE64,
                $fileName
            );
        }
    }

    /**
     * @param Order $order
     */
    public function createShipment($order)
    {
        try {
            $payment = $order->getPayment();
            $method = $payment->getMethodInstance();
            $methodCode = $method->getCode();
            if (!$order->canShip() && $methodCode !== "paypal_express") {
                return;
            }
            if ($this->helper->isEnablePartial()) {
                $count = 0;
                foreach ($order->getAllItems() as $item) {
                    if ($this->partialHelper->validateProduct($item->getProduct())) {
                        $count++;
                    }
                }
                if ($count === 0) {
                    return;
                }
            }
            $orderShipment = $this->orderConverter->toShipment($order);
            if ($this->multiSourceInventory->isEnabledMsi()) {
                $websiteCode = $order->getStore()->getWebsite()->getCode();
                $stockId = $this->multiSourceInventory->getStockResolverObject()
                    ->execute('website', $websiteCode)->getStockId();
                $sources = $this->multiSourceInventory->getSourcesAssignedToStockOrderedByPriority()
                    ->execute((int)$stockId);
                if (!empty($sources) && count($sources) == 1) {
                    $sourceCode = $sources[0]->getSourceCode();
                } else {
                    $sourceCode = $this->multiSourceInventory->getDefaultSourceProvider()->getCode();
                }
                $orderShipment->getExtensionAttributes()->setSourceCode($sourceCode);
            }
            //add order shipment item
            $this->addItemShipment($order, $orderShipment);
            $orderShipment->register();
            $orderShipment->getOrder()->setIsInProcess(true);
            $orderShipment->getExtensionAttributes()->setSourceCode('default');

            // Save created Order Shipment
            $savedShipment = $orderShipment->save();
            $orderShipment->getOrder()->save();

            //check send pdf shipment in email
            $this->helper->getTransportBuilder()->resetBuilder();
            if ($this->helper->enableSendShipmentPdf()) {
                $shipmentClass = $this->helper->getPdfShipmentClass();
                $fileName = 'shipment-' . $orderShipment->getId() . '.pdf';
                $this->checkSendPdfFile($shipmentClass, $orderShipment, $fileName);
            }

            //Set item to order
            if ($this->productMetadata->getVersion() > "2.3.6") {
                $this->shippingOrder->setItemsOrder($order);
            }
            $this->sendShipment($orderShipment);
            if ($savedShipment) {
                if ($methodCode === "amazon_payment" && $this->helper->getSelectCapture() === "online") {
                    $orderIncrementId = $order->getIncrementId();
                    $order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);
                    $currentState = $order->getState();
                    if ($currentState === Order::STATE_PROCESSING && !$order->canShip()) {
                        $order->setState(Order::STATE_COMPLETE)
                            ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_COMPLETE));
                    }
                }
                $order->addStatusHistoryComment(
                    __('Automatically Shipment by BSS AutoInvoice.'),
                    false
                );
                $order->save();
            }
        } catch (\Exception $e) {
            $this->helper->returnLogger()->error($e->getMessage());
        }
    }

    /**
     * @param Order $order
     * @param mixed $orderShipment
     * @return mixed
     * @throws LocalizedException
     */
    protected function addItemShipment($order, &$orderShipment)
    {
        foreach ($order->getAllItems() as $orderItem) {
            if ($this->helper->isEnablePartial()) {
                if ($this->partialHelper->validateProduct($orderItem->getProduct())) {
                    if ($orderItem->getParentItemId()) {
                        $orderItem = $orderItem->getParentItem();
                    }
                    $qty = $orderItem->getQtyToShip();
                    $shipmentItem = $this->orderConverter->itemToShipmentItem($orderItem)->setQty($qty);
                    $orderShipment->addItem($shipmentItem);
                    continue;
                }
                continue;
            }

            // is virtual item and item qty valid
            if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                continue;
            }

            $qty = $orderItem->getQtyToShip();
            $shipmentItem = $this->orderConverter->itemToShipmentItem($orderItem)->setQty($qty);

            $orderShipment->addItem($shipmentItem);
        }
        return $orderShipment;
    }

    /**
     * Send Shipment
     *
     * @param mixed $shipment
     */
    protected function sendShipment($shipment)
    {
        if ($shipment) {
            try {
                $this->shipmentSender->send($shipment);
            } catch (\Exception $e) {
                $this->helper->returnLogger()->error($e->getMessage());
            }
        }
    }

    /**
     * @param Invoice $invoice
     * @throws LocalizedException
     */
    protected function checkInvoice($invoice)
    {
        if (!$invoice) {
            throw new LocalizedException(__('We can\'t save the invoice right now.'));
        }

        if (!$invoice->getTotalQty()) {
            throw new LocalizedException(
                __('You can\'t create an invoice without products.')
            );
        }
    }

    /**
     * Get Shipment By Version
     *
     * @return ShipmentDocumentFactory|ShipmentFactory
     */
    protected function getShipmentByVersion()
    {
        if ($this->helper->checkMagentoVersion()) {
            return $this->shipmentFactory;
        } else {
            return $this->shipmentDocumentFactory;
        }
    }

    /**
     * Check Payment is Klarna Payment and Capture is online
     *
     * @param $methodCode
     * @param $selectCapture
     * @return bool
     */
    public function checkKlarnaPayment($methodCode, $selectCapture)
    {
        if ($methodCode === "klarna_kco" && $selectCapture === "online") {
            return true;
        }
        return false;
    }
}
