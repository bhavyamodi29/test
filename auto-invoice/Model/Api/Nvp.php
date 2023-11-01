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
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AutoInvoice\Model\Api;

use Magento\Paypal\Model\Api\ProcessableException;

class Nvp extends \Magento\Paypal\Model\Api\Nvp
{
    /**
     * Handle logical errors
     *
     * @param array $response
     * @return void
     * @throws \Magento\Paypal\Model\Api\ProcessableException|\Magento\Framework\Exception\LocalizedException
     */
    protected function _handleCallErrors($response)
    {
        $errors = $this->_extractErrorsFromResponse($response);
        if (empty($errors)) {
            return;
        }

        $callErrors = $this->_callErrors;
        foreach ($errors as $error) {
            $errorMessages[] = $error['message'];
            $callErrors[] = $error['code'];
        }
        $errorMessages = implode(' ', $errorMessages);

        $exceptionLogMessage = sprintf(
            'PayPal NVP gateway errors: %s Correlation ID: %s. Version: %s.',
            $errorMessages,
            isset($response['CORRELATIONID']) ? $response['CORRELATIONID'] : '',
            isset($response['VERSION']) ? $response['VERSION'] : ''
        );
        $this->_logger->critical($exceptionLogMessage);

        /**
         * The response code 10415 'Transaction has already been completed for this token'
         * must not fail place order. The old PayPal interface does not lock 'Send' button
         * it may result to re-send data.
         */
        if (in_array((string)ProcessableException::API_TRANSACTION_HAS_BEEN_COMPLETED, $callErrors)) {
            return;
        }

        parent::_handleCallErrors($response);
    }
}
