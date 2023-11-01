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
 * @copyright Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AutoInvoice\Model\Plugin\Paypal;

use Magento\Checkout\Model\Session;
use Magento\Payment\Model\InfoInterface;
use Magento\Paypal\Model\Pro;

class Express
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Pro
     */
    protected $pro;

    /**
     * @param Session $session
     * @param Pro $pro
     */
    public function __construct(
        \Magento\Checkout\Model\Session $session,
        \Magento\Paypal\Model\Pro $pro
    )
    {
        $this->session = $session;
        $this->pro = $pro;
    }

    /**
     * Set data transaction when place order.
     *
     * @param \Magento\Paypal\Model\Express $subject
     * @param InfoInterface $payment
     * @param float $amount
     * @return void
     */
    public function beforeOrder(
        \Magento\Paypal\Model\Express $subject,
        \Magento\Payment\Model\InfoInterface $payment,
        $amount
    ) {
        $paypalTransactionData = $this->session->getPaypalTransactionData();
        if (isset($paypalTransactionData['processable_errors'])) {
            $this->session->setPaypalTransactionData(null);
        }
    }
}
