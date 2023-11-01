<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bss\AutoInvoice\Api;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Partial invoice rule CRUD interface
 *
 * @api
 * @since 100.0.2
 */
interface RuleRepositoryInterface
{
    /**
     * Save sales rule.
     *
     * @param \Bss\AutoInvoice\Api\Data\RuleInterface $rule
     * @return \Magento\SalesRule\Api\Data\RuleInterface
     * @throws \Magento\Framework\Exception\InputException If there is a problem with the input
     * @throws \Magento\Framework\Exception\NoSuchEntityException If a rule ID is sent but the rule does not exist
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Bss\AutoInvoice\Api\Data\RuleInterface $rule);

    /**
     * Retrieve sales rules that match te specified criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included. See https://devdocs.magento.com/codelinks/attributes.html#RuleRepositoryInterface to
     * determine which call to use to get detailed information about all attributes for an object.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\SalesRule\Api\Data\RuleSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete rule by ID.
     *
     * @param int $ruleId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($ruleId);

    /**
     * Get rule by store id
     *
     * @param int $storeId
     * @return \Bss\AutoInvoice\Api\Data\RuleInterface
     * @throws NoSuchEntityException
     */
    public function getByStoreId($storeId);

    /**
     * Get rule by id
     *
     * @param int $id
     * @return \Bss\AutoInvoice\Model\Rule
     */
    public function getById($id);
}
