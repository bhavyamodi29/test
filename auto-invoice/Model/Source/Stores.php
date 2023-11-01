<?php
declare(strict_types=1);

/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_AutoInvoice
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AutoInvoice\Model\Source;

class Stores implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Store\Model\ResourceModel\Group\CollectionFactory
     */
    protected $storeGroups;

    /**
     * Stores constructor.
     * @param \Magento\Store\Model\ResourceModel\Group\CollectionFactory $storeGroups
     */
    public function __construct(
        \Magento\Store\Model\ResourceModel\Group\CollectionFactory $storeGroups
    ) {
        $this->storeGroups = $storeGroups;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [['label' => __('All Stores'), 'value' => 0]];
        $storeGroups = $this->storeGroups->create();
        if ($storeGroups->getSize()) {
            foreach ($storeGroups as $item) {
                $stores = $item->getStores();
                foreach ($stores as $store) {
                    $options[] = ['label' => $store->getName(), 'value' => $store->getStoreId()];//get store view name
                }
            }
        }

        return $options;
    }
}
