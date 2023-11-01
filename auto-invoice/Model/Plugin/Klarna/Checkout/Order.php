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
 * @copyright  Copyright (c) 2022-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AutoInvoice\Model\Plugin\Klarna\Checkout;

use Bss\AutoInvoice\Helper\AutoInvoice as AutoInvoiceHelper;
use Bss\AutoInvoice\Helper\Data;
use Bss\AutoInvoice\Model\KlarnaRepository;
use Magento\Sales\Api\Data\OrderInterface as MagentoOrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class Order
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
     * @var \Klarna\Kco\Model\Checkout\Order
     */
    protected $klarnaOrder;

    /**
     * @var \Bss\AutoInvoice\Model\KlarnaRepository
     */
    protected $klarnaRepository;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @param Data $helper
     * @param AutoInvoiceHelper $autoInvoiceHelper
     * @param KlarnaRepository $klarnaRepository
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Data $helper,
        AutoInvoiceHelper $autoInvoiceHelper,
        \Bss\AutoInvoice\Model\KlarnaRepository $klarnaRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->helper = $helper;
        $this->autoInvoiceHelper = $autoInvoiceHelper;
        $this->klarnaRepository = $klarnaRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param \Klarna\Kco\Model\Checkout\Order $subject
     * @param MagentoOrderInterface $result
     * @param string $klarnaOrderId
     * @return MagentoOrderInterface
     */
    public function afterCreateMagentoOrder(
        \Klarna\Kco\Model\Checkout\Order $subject,
        $result,
        $klarnaOrderId
    ) {
        $klarnaOrder = $this->klarnaRepository->createKlarnaOrder()->load($klarnaOrderId, 'klarna_order_id');
        if ($klarnaOrder->getOrderId()) {
            $orderId = $klarnaOrder->getOrderId();
            $order = $this->orderRepository->get($orderId);

            if (!$order->getId() || !$this->helper->checkPaymentmethod($order->getPayment()->getMethodInstance()->getCode())) {
                return $result;
            }
            //Update Payment status
            $this->klarnaRepository->getKlarnaOrder()->checkAndUpdateOrderState($order->getId());

            // Create invoice and shipment
            $this->doAutoInvoice($order);
        }

        return $result;
    }

    /**
     * Create invoice and shipment
     *
     * @param \Magento\Sales\Model\Order $order
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function doAutoInvoice($order)
    {
        if ($this->helper->checkStateOrder($order)) {
            if ($this->helper->isConfiginvoice() && $order->canInvoice()) {
                $this->autoInvoiceHelper->createInvoice($order);
            }

            if ($this->helper->enabledShipment()) {
                $this->autoInvoiceHelper->createShipment($order);
            }
        }
    }
}
