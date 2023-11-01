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
 * @category  BSS
 * @package   Bss_AutoInvoice
 * @author    Extension Team
 * @copyright Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AutoInvoice\Model;

use Bss\AutoInvoice\Helper\Partial;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Rule\Model\AbstractModel;

/**
 * Class Rule
 *
 * @package Bss\AutoInvoice\Model
 */
class Rule extends AbstractModel implements \Bss\AutoInvoice\Api\Data\RuleInterface
{
    const STATUS_ENABLED = 1;

    const STATUS_DISABLED = 0;

    /**
     * @var Partial
     */
    protected $partialHelper;

    /**
     * @param \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $condCombineFactory
     * @param \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $conditionProduct
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param Partial $partialHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param ExtensionAttributesFactory|null $extensionFactory
     * @param AttributeValueFactory|null $customAttributeFactory
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     */
    public function __construct(
        \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $condCombineFactory,
        \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $conditionProduct,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        Partial $partialHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        ExtensionAttributesFactory $extensionFactory = null,
        AttributeValueFactory $customAttributeFactory = null,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->partialHelper = $partialHelper;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data,
            $extensionFactory,
            $customAttributeFactory,
            $serializer
        );
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(\Bss\AutoInvoice\Model\ResourceModel\Rule::class);
    }

    /**
     * Returns rule id field
     *
     * @return int|null
     * @since 100.1.0
     */
    public function getRuleId()
    {
        return $this->getData(self::RULE_ID);
    }

    /**
     * @param int $ruleId
     * @return void
     * @since 100.1.0
     */
    public function setRuleId($ruleId)
    {
        $this->setData(self::RULE_ID, $ruleId);
    }

    /**
     * Returns rule name
     *
     * @return string
     * @since 100.1.0
     */
    public function getRuleName()
    {
        return $this->getData(self::RULE_NAME);
    }

    /**
     * @param string $name
     * @return $this
     * @since 100.1.0
     */
    public function setRuleName($name)
    {
        return $this->setData(self::RULE_NAME, $name);
    }

    /**
     * Returns rule activity flag
     *
     * @return int
     * @since 100.1.0
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @param int $isActive
     * @return $this
     * @since 100.1.0
     */
    public function setStatus($isActive)
    {
        return $this->setData(self::STATUS, $isActive);
    }

    /**
     * Returns condition
     *
     * @return string
     * @since 100.1.0
     */
    public function getConditionSearialized()
    {
        return $this->getData(self::CONDITIONS_SERIALIZED);
    }

    /**
     * @param string $condition
     * @return $this
     * @since 100.1.0
     */
    public function setConditionSearialized($condition)
    {
        return $this->setData(self::CONDITIONS_SERIALIZED, $condition);
    }

    /**
     * Returns rule activity flag
     *
     * @return int
     * @since 100.1.0
     */
    public function getPriority()
    {
        return $this->getData(self::PRIORITY);
    }

    /**
     * @param int $priority
     * @return $this
     * @since 100.1.0
     */
    public function setPriority($priority)
    {
        return $this->setData(self::PRIORITY, $priority);
    }

    /**
     * Returns rule activity flag
     *
     * @return int
     * @since 100.1.0
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * @param int $storeId
     * @return $this
     * @since 100.1.0
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * @return \Magento\CatalogRule\Model\Rule\Condition\Combine|\Magento\Rule\Model\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->partialHelper->getCondCombineFactory()->create();
    }

    /**
     * @return \Magento\CatalogRule\Model\Rule\Condition\Product|\Magento\Rule\Model\Action\Collection
     */
    public function getActionsInstance()
    {
        return $this->partialHelper->getConditionProduct()->create();
    }

    /**
     * Check rule is allowed to apply
     *
     * @return bool
     */
    public function isApplyRule()
    {
        return (bool)$this->getStatus();
    }
}
