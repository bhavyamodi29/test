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
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AutoInvoice\Model\Observer;

use Bss\AutoInvoice\Helper\AutoInvoice as AutoInvoiceHelper;
use Bss\AutoInvoice\Helper\Data;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class AutoInvoice implements ObserverInterface
{
    /**
     * Helper Data
     *
     * @var Data $helper
     */
    protected $helper;

    /**
     * @var AutoInvoiceHelper
     */
    protected $autoInvoiceHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /**
     * AutoInvoice constructor.
     * @param Data $helper
     * @param AutoInvoiceHelper $autoInvoiceHelper
     * @param \Magento\Framework\UrlInterface $urlInterface
     */
    public function __construct(
        Data $helper,
        AutoInvoiceHelper $autoInvoiceHelper,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->helper = $helper;
        $this->autoInvoiceHelper = $autoInvoiceHelper;
        $this->urlInterface = $urlInterface;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return bool|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if ($this->helper->isEnabled()) {
                /** @var Order $order */
                $orders = [];
                $currentUrl = $this->urlInterface->getCurrentUrl();
                $orders[] = $observer->getEvent()->getOrder();
                if (strpos($currentUrl, 'multishipping') !== false) {
                    $orders = $observer->getEvent()->getOrders();
                }
                foreach ($orders as $order) {
                    if (!$order->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The order is no longer exists.'));
                    }

                    // Create invoice and shipment
                    if (!$this->helper->checkPaymentmethod($order->getPayment()->getMethodInstance()->getCode())) {
                        return;
                    }
                    if (!$order->getPayment()->getMethodInstance()->isOffline()
                        && !$order->getPayment()->getLastTransId()
                        && $order->getPayment()->getMethod() !== "free") {
                        return;
                    }
                    if ($order->getPayment()->getMethod() != "klarna_kco") {
                        $this->doAutoInvoice($order);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->helper->returnLogger()->error($e->getMessage());
        }
    }

    /**
     * @param Order $order
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function doAutoInvoice($order)
    {
        $paymentCode = $order->getPayment()->getMethodInstance()->getCode();
        switch ($paymentCode):
            case \Magento\Paypal\Model\Config::METHOD_EXPRESS:
                break;
            default:
                if ($paymentCode === "klarna_kco") {
                    $order->setState(Order::STATE_PROCESSING);
                }
                if ($this->helper->checkStateOrder($order)) {
                    if ($this->helper->isConfiginvoice() && $order->canInvoice()) {
                        $this->autoInvoiceHelper->createInvoice($order);
                    }
                    if ($this->helper->enabledShipment()) {
                        $this->autoInvoiceHelper->createShipment($order);
                    }
                }
                break;
        endswitch;
    }
}
