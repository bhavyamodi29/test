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
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AutoInvoice\Helper;

use Magento\Framework\App\Helper\Context;

class MultiSourceInventory extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Const
     */
    const MSI_MODULE_CORE = 'Magento_Inventory';

    /**
     * @var MultiSourceInventoryFactory
     */
    protected $factory;

    /**
     * @var mixed
     */
    protected $getSourcesAssignedToStockOrderedByPriority = null;

    /**
     * @var mixed
     */
    protected $defaultSourceProvider = null;

    /**
     * @var mixed
     */
    protected $stockResolver = null;

    /**
     * MultiSourceInventory constructor.
     * @param Context $context
     * @param MultiSourceInventoryFactory $factory
     */
    public function __construct(
        Context $context,
        MultiSourceInventoryFactory $factory
    ) {
        parent::__construct($context);
        $this->factory = $factory;
    }

    /**
     * @return bool
     */
    public function isEnabledMsi()
    {
        return $this->_moduleManager->isEnabled(self::MSI_MODULE_CORE);
    }

    /**
     * @return \Magento\InventoryApi\Api\GetSourcesAssignedToStockOrderedByPriorityInterface
     */
    public function getSourcesAssignedToStockOrderedByPriority()
    {
        if (!$this->getSourcesAssignedToStockOrderedByPriority) {
            $this->getSourcesAssignedToStockOrderedByPriority = $this->factory->create(
                \Magento\InventoryApi\Api\GetSourcesAssignedToStockOrderedByPriorityInterface::class
            );
        }
        return $this->getSourcesAssignedToStockOrderedByPriority;
    }

    /**
     * @return \Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface
     */
    public function getDefaultSourceProvider()
    {
        if (!$this->defaultSourceProvider) {
            $this->defaultSourceProvider = $this->factory->create(
                \Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface::class
            );
        }
        return $this->defaultSourceProvider;
    }

    /**
     * @return \Magento\InventorySalesApi\Api\StockResolverInterface
     */
    public function getStockResolverObject()
    {
        if (!$this->stockResolver) {
            $this->stockResolver = $this->factory->create(
                \Magento\InventorySalesApi\Api\StockResolverInterface::class
            );
        }
        return $this->stockResolver;
    }
}
