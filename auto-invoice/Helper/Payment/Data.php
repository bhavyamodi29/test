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
namespace Bss\AutoInvoice\Helper\Payment;

class Data extends \Magento\Payment\Helper\Data
{
    /**
     * Get method
     *
     * @param mixed $code
     * @param array $data
     * @param null|int $store
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getMethods($code, $data, $store = null)
    {
        if (isset($data['title'])) {
            return $data['title'];
        }
        return $this->getMethodInstance($code)->getConfigData('title', $store);
    }

    /**
     * Sort
     *
     * @param mixed $methods
     * @param mixed $sorted
     * @return array
     */
    protected function sort($methods, $sorted)
    {
        if ($sorted) {
            asort($methods);
        }
        return $methods;
    }

    /**
     * Get Payment Method List
     *
     * @param bool $sorted
     * @param bool $asLabelValue
     * @param bool $withGroups
     * @param null|int $store
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPaymentMethodList($sorted = true, $asLabelValue = false, $withGroups = false, $store = null)
    {
        $methods = [];
        $groups = [];
        $groupRelations = [];

        foreach ($this->getPaymentMethods() as $code => $data) {
            if (!isset($data['group']) && $code != "amazon_payment_v2") {
                continue;
            }
            $methods[$code] = $this->getMethods($code, $data, $store);
            if ($asLabelValue && $withGroups && isset($data['group'])) {
                $groupRelations[$code] = $data['group'];
            }
        }
        if ($asLabelValue && $withGroups) {
            $groups = $this->_paymentConfig->getGroups();
            foreach ($groups as $code => $title) {
                $methods[$code] = $title;
            }
        }
        $methods = $this->sort($methods, $sorted);
        if ($asLabelValue) {
            return $this->returnLabelValue($methods, $groupRelations, $groups);
        }

        return $methods;
    }

    /**
     * Get label value payment
     *
     * @param array $methods
     * @param array $groupRelations
     * @param array $groups
     * @return array
     */
    protected function returnLabelValue($methods, $groupRelations, $groups)
    {
        $labelValues = [];
        foreach ($methods as $code => $title) {
            $labelValues[$code] = [];
        }
        foreach ($methods as $code => $title) {
            if (isset($groups[$code])) {
                $labelValues[$code]['label'] = $title;
            } elseif (isset($groupRelations[$code])) {
                unset($labelValues[$code]);
                $labelValues[$groupRelations[$code]]['value'][$code] = ['value' => $code, 'label' => $title];
            } else {
                $labelValues[$code] = ['value' => $code, 'label' => $title];
            }
        }
        return $labelValues;
    }
}
