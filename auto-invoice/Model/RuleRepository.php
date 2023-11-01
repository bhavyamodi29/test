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
 * @package    Bss_CompanyCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AutoInvoice\Model;

use Bss\AutoInvoice\Api\RuleRepositoryInterface;
use Bss\AutoInvoice\Api\Data\RuleInterface;
use Bss\AutoInvoice\Model\ResourceModel\Rule as RuleResource;
use Bss\AutoInvoice\Model\ResourceModel\Rule\CollectionFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor;

class RuleRepository implements RuleRepositoryInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var RuleResource
     */
    protected $ruleResource;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var SearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var CollectionProcessor
     */
    private $collectionProcessor;

    /**
     * @param LoggerInterface $logger
     * @param RuleResource $ruleResource
     * @param \Bss\AutoInvoice\Model\RuleFactory $ruleFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessor $collectionProcessor
     */
    public function __construct(
        LoggerInterface $logger,
        RuleResource $ruleResource,
        RuleFactory $ruleFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        CollectionProcessor $collectionProcessor
    ) {
        $this->logger = $logger;
        $this->ruleResource = $ruleResource;
        $this->ruleFactory = $ruleFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Save rule
     *
     * @param RuleInterface $ruleInterface
     * @return void
     * @throws CouldNotSaveException
     */
    public function save($ruleInterface)
    {
        try {
            $this->ruleResource->save($ruleInterface);
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
            throw new CouldNotSaveException(
                __('Could not save company credit')
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getList($criteria, $with = null)
    {
        $searchResult = $this->searchResultsFactory->create();
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($criteria, $collection);

        if ($with) {
            $collection->with($with);
        }

        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * Delete rule
     *
     * @param RuleInterface $rule
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete($rule)
    {
        try {
            $this->ruleResource->delete($rule);
            return true;
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __('%1', $e->getMessage())
            );
        }
    }

    /**
     * Delete rule by id
     *
     * @param int $id
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteById($id)
    {
        try {
            $rule = $this->ruleFactory->create();
            $this->ruleResource->load($rule, $id);
            if ($rule->getId()) {
                $this->delete($rule);
                return true;
            }
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __(
                    'Could not delete rule: %1',
                    $e->getMessage()
                )
            );
        }
        throw new CouldNotDeleteException(__('Rule id %1 not exists ', $id));
    }

    /**
     * Get rule with the highest priority by store id
     *
     * @param int $storeId
     * @return \Bss\AutoInvoice\Model\Rule|bool|\Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function getByStoreId($storeId)
    {
        try {
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter(
                RuleInterface::STORE_ID, $storeId)->addFieldToFilter(
                RuleInterface::STATUS, 1)
                ->setOrder(RuleInterface::PRIORITY, 'ASC');
            if (count($collection->getItems()) > 0)
            {
                foreach ($collection->getItems() as $item)
                {
                    if ($item->getPriority() > 0) return $item;
                }
                return $collection->getFirstItem();
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new \Magento\Framework\Exception\NotFoundException(
                __('Can not get this rule.')
            );
        }
        return false;
    }

    /**
     * Get rule by id
     *
     * @param int $id
     * @return \Bss\AutoInvoice\Model\Rule
     */
    public function getById($id)
    {
        $rule = $this->ruleFactory->create();
        try {
            $this->ruleResource->load($rule, $id);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return $rule;
    }
}
