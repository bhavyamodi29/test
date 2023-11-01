<?php
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
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);
namespace Bss\AutoInvoice\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;

class KlarnaRepository
{
    /**
     * @var mixed|null
     */
    private $klarnaOrder;

    /**
     * @var mixed|null
     */
    private $klarnaRepository;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Manager $moduleManager
     * @param null $klarnaRepository
     * @param null $klarnaOrder
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Manager $moduleManager,
        $klarnaRepository = null,
        $klarnaOrder = null
    ) {
        $this->objectManager = $objectManager;
        $this->moduleManager = $moduleManager;
        $this->klarnaRepository = $klarnaRepository;
        $this->klarnaOrder = $klarnaOrder;
    }

    /**
     * Create Klarna Order using Object Manager
     *
     * @return mixed
     */
    public function createKlarnaOrder()
    {
        return $this->getKlarnaRepository()->create();
    }

    /**
     * Save Klarna Order
     *
     * @param $orderId
     * @param $klarnaId
     */
    public function saveKlarnaOrder($orderId, $klarnaId)
    {
        try {
            $orderKlarna = $this->createKlarnaOrder();
            $orderKlarna->setKlarnaOrderId($klarnaId);
            $orderKlarna->setOrderId($orderId);
            $orderKlarna->setReservationId($klarnaId);
            $orderKlarna->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }
    }

    /**
     * Check module Kco is enabled
     *
     * @return bool
     */
    public function isKcoEnabled()
    {
        return $this->moduleManager->isEnabled('Klarna_Kco');
    }

    /**
     * @param string $objectName
     * @param array $data
     * @return object|null
     */
    public function getObjectInstance($objectName, $data = [])
    {
        if ($this->isKcoEnabled()) {
            return $this->objectManager->create(
                $objectName,
                $data
            );
        }
        return null;
    }

    /**
     * @param array $data
     * @return object|null
     */
    public function getKlarnaRepository($data = [])
    {
        return $this->getObjectInstance(
            $this->klarnaRepository,
            $data
        );
    }

    /**
     * @param array $data
     * @return object|null
     */
    public function getKlarnaOrder($data = [])
    {
        return $this->getObjectInstance(
            $this->klarnaOrder,
            $data
        );
    }
}
