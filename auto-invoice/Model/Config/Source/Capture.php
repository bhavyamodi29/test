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
namespace Bss\AutoInvoice\Model\Config\Source;

class Capture implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * To Option Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $capture = [
            ['value' => \Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE, 'label' => __('Capture Online')],
            ['value' => \Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE, 'label' => __('Capture Offline')],
            ['value' => \Magento\Sales\Model\Order\Invoice::NOT_CAPTURE, 'label' => __('Not Capture')]
        ];
        return $capture;
    }
}
