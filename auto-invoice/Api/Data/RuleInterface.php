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
 * @copyright  Copyright (c) 2020-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AutoInvoice\Api\Data;

/**
 * @api
 * @since 100.1.0
 */
interface RuleInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const RULE_ID = 'entity_id';

    const RULE_NAME = 'rule_name';

    const STATUS = 'status';

    const CONDITIONS_SERIALIZED = 'conditions_serialized';

    const PRIORITY = 'priority';

    const STORE_ID = 'store_id';
    /**#@-*/

    /**
     * Returns rule id field
     *
     * @return int|null
     * @since 100.1.0
     */
    public function getRuleId();

    /**
     * @param int $ruleId
     * @return $this
     * @since 100.1.0
     */
    public function setRuleId($ruleId);

    /**
     * Returns rule name
     *
     * @return string
     * @since 100.1.0
     */
    public function getRuleName();

    /**
     * @param string $name
     * @return $this
     * @since 100.1.0
     */
    public function setRuleName($name);

    /**
     * Returns rule status
     *
     * @return int
     * @since 100.1.0
     */
    public function getStatus();

    /**
     * @param int $isActive
     * @return $this
     * @since 100.1.0
     */
    public function setStatus($isActive);

    /**
     * Returns condition
     *
     * @return string
     * @since 100.1.0
     */
    public function getConditionSearialized();

    /**
     * @param string $condition
     * @return $this
     * @since 100.1.0
     */
    public function setConditionSearialized($condition);

    /**
     * Returns rule priority
     *
     * @return int
     * @since 100.1.0
     */
    public function getPriority();

    /**
     * @param int $priority
     * @return $this
     * @since 100.1.0
     */
    public function setPriority($priority);

    /**
     * Returns rule store id
     *
     * @return int
     * @since 100.1.0
     */
    public function getStoreId();

    /**
     * @param int $storeId
     * @return $this
     * @since 100.1.0
     */
    public function setStoreId($storeId);
}
