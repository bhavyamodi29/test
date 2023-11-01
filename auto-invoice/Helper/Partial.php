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
namespace Bss\AutoInvoice\Helper;

use Bss\AutoInvoice\Api\RuleRepositoryInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\LayoutFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class Partial extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\CombineFactory
     */
    protected $condCombineFactory;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\ProductFactory
     */
    protected $conditionProduct;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Iterator
     */
    protected $iterator;

    /**
     * @var RuleRepositoryInterface
     */
    protected $ruleRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Data constructor.
     * @param Context $context
     * @param LayoutFactory $layoutFactory
     * @param CollectionFactory $productCollectionFactory
     * @param \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $condCombineFactory
     * @param \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $conditionProduct
     * @param \Magento\Framework\Model\ResourceModel\Iterator $iterator
     * @param RuleRepositoryInterface $ruleRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        LayoutFactory $layoutFactory,
        CollectionFactory $productCollectionFactory,
        \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $condCombineFactory,
        \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $conditionProduct,
        \Magento\Framework\Model\ResourceModel\Iterator $iterator,
        RuleRepositoryInterface $ruleRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->condCombineFactory = $condCombineFactory;
        $this->conditionProduct = $conditionProduct;
        $this->iterator = $iterator;
        $this->ruleRepository = $ruleRepository;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Get layout
     *
     * @return LayoutFactory
     */
    public function getLayoutFactory()
    {
        return $this->layoutFactory;
    }

    /**
     * Get product collection
     *
     * @return CollectionFactory
     */
    public function getProductCollectionFactory()
    {
        return $this->productCollectionFactory;
    }

    /**
     * Get condition combine
     *
     * @return \Magento\CatalogRule\Model\Rule\Condition\CombineFactory
     */
    public function getCondCombineFactory()
    {
        return $this->condCombineFactory;
    }

    /**
     * Get condition product
     *
     * @return \Magento\CatalogRule\Model\Rule\Condition\ProductFactory
     */
    public function getConditionProduct()
    {
        return $this->conditionProduct;
    }

    /**
     * Get iterator
     *
     * @return \Magento\Framework\Model\ResourceModel\Iterator
     */
    public function getIterator()
    {
        return $this->iterator;
    }

    /**
     * Validate product that satisfied the rule
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Catalog\Api\Data\ProductInterface $product
     * @return bool
     */
    public function validateProduct($product)
    {
        try {
            $storeId = $this->getStoreId();
            $rule = $this->ruleRepository->getByStoreId($storeId);
            return $rule && $rule->isApplyRule() && $rule->getConditions()->validate($product);
        } catch (NoSuchEntityException $exception) {
            $this->_logger->critical($exception);
        }
        return false;
    }

    /**
     * Get store id
     *
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId() ?? '';
    }
}
