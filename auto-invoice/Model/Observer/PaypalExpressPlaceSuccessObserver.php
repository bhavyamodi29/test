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

namespace Bss\AutoInvoice\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Bss\AutoInvoice\Helper\Data;
use Bss\AutoInvoice\Helper\AutoInvoice;
use Magento\Paypal\Model\Adminhtml\Express;
use Magento\Paypal\Model\Api\ProcessableException;
use Magento\Sales\Model\Order;
use Magento\Payment\Model\Method\AbstractMethod;

class PaypalExpressPlaceSuccessObserver implements ObserverInterface
{
    /**
     * Helper Data
     *
     * @var Data $helper
     */
    private $helper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var AutoInvoice
     */
    private $autoInvoice;

    /**
     * @var Express
     */
    private $expressAuthorization;

    /**
     * PaypalExpressPlaceSuccessObserver constructor.
     * @param Data $helper
     * @param AutoInvoice $autoInvoice
     * @param Express $expressAuthorization
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        Data $helper,
        AutoInvoice $autoInvoice,
        Express $expressAuthorization,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->autoInvoice = $autoInvoice;
        $this->expressAuthorization = $expressAuthorization;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->isEnabled()) {
            /** @var Order $order */
            $order = $observer->getEvent()->getOrder();

            if (!$order->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('The order is no longer exists.'));
            }
            //Create invoice and shipment
            $payment = $order->getPayment();
            $paymentCode = $payment->getMethodInstance()->getCode();

            if (!$payment->getMethodInstance()->isOffline() && !$payment->getLastTransId()) {
                return;
            }
            if ($this->helper->checkPaymentmethod($paymentCode) && $this->helper->checkStateOrder($order)
            ) {
                try {
                    if ($this->helper->isConfiginvoice()) {
                        $this->doAuthorize($order);
                    }

                    if ($this->helper->enabledShipment()) {
                        $this->autoInvoice->createShipment($order);
                    }
                } catch (\Exception $e) {
                    $this->helper->returnLogger()->error($e->getMessage());
                }
            }
        }
    }

    /**
     * Do Authorize
     *
     * @param Order $order
     * @return false|\Magento\Framework\DB\Transaction|void|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function doAuthorize($order)
    {
        /**
         * Handle with Paypal Express method with Payment action is Order
         * auto authorization -> create invoice
         */
        $payment = $order->getPayment();

        if ($payment->getMethodInstance()->getConfigPaymentAction() === AbstractMethod::ACTION_ORDER) {
            if ($this->expressAuthorization->isOrderAuthorizationAllowed($payment)) {
                $this->expressAuthorization->authorizeOrder($order);
                return $this->autoInvoice->createInvoice($order);
            }
        } elseif ($payment->getMethodInstance()->getConfigPaymentAction() === AbstractMethod::ACTION_AUTHORIZE) {
            $this->expressAuthorization->authorize($payment, $order->getBaseTotalDue());
            return $this->autoInvoice->createInvoice($order);
        } else {
            if ($order->canInvoice()) {
                return $this->autoInvoice->createInvoice($order);
            }
        }
    }
}
