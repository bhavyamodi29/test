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
namespace Bss\AutoInvoice\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Model\Order;

class ShippingOrder
{
    /**
     * @var OrderItemRepositoryInterface
     */
    private $itemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param OrderItemRepositoryInterface|null $itemRepository
     * @param SearchCriteriaBuilder|null $searchCriteriaBuilder
     */
    public function __construct(
        OrderItemRepositoryInterface $itemRepository = null,
        SearchCriteriaBuilder $searchCriteriaBuilder = null
    ) {
        $this->itemRepository = $itemRepository ?: ObjectManager::getInstance()
            ->get(OrderItemRepositoryInterface::class);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder ?: ObjectManager::getInstance()
            ->get(SearchCriteriaBuilder::class);
    }

    /**
     * Set items order when send email shipping
     *
     * @param Order $order
     * @return void
     */
    public function setItemsOrder($order)
    {
        $this->searchCriteriaBuilder->addFilter(OrderItemInterface::ORDER_ID, $order->getId());
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $order->setItems($this->itemRepository->getList($searchCriteria)->getItems());
    }
}
