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

namespace Bss\AutoInvoice\Mail\Template;

use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\MessageInterfaceFactory;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;
use Zend\Mime\PartFactory as MimePartFactory;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\MessageFactory as MimeMessageFactory;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    /** @var MimePart[] */
    private $parts = [];

    /** @var MimeMessageFactory */
    private $mimeMessageFactory;

    /** @var MimePartFactory */
    private $mimePartFactory;

    /**
     * TransportBuilder constructor.
     * @param FactoryInterface $templateFactory
     * @param MessageInterface $message
     * @param SenderResolverInterface $senderResolver
     * @param ObjectManagerInterface $objectManager
     * @param TransportInterfaceFactory $mailTransportFactory
     * @param MimePartFactory $mimePartFactory
     * @param MimeMessageFactory $mimeMessageFactory
     * @param MessageInterfaceFactory|null $messageFactory
     */
    public function __construct(
        FactoryInterface $templateFactory,
        MessageInterface $message,
        SenderResolverInterface $senderResolver,
        ObjectManagerInterface $objectManager,
        TransportInterfaceFactory $mailTransportFactory,
        MimePartFactory $mimePartFactory,
        MimeMessageFactory $mimeMessageFactory,
        MessageInterfaceFactory $messageFactory = null
    ) {
        parent::__construct(
            $templateFactory,
            $message,
            $senderResolver,
            $objectManager,
            $mailTransportFactory,
            $messageFactory
        );

        $this->mimePartFactory    = $mimePartFactory;
        $this->mimeMessageFactory = $mimeMessageFactory;
    }

    /**
     * @return $this|TransportBuilder
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function prepareMessage()
    {
        parent::prepareMessage();
        $mimeMessage = $this->getMimeMessage($this->message);
        foreach ($this->parts as $part) {
            $mimeMessage->addPart($part);
        }
        $this->message->setBody($mimeMessage);

        return $this;
    }

    /**
     * @param mixed $body
     * @param string $mimeType
     * @param string $disposition
     * @param string $encoding
     * @param null $filename
     * @return $this
     */
    public function addAttachment(
        $body,
        $mimeType = Mime::TYPE_OCTETSTREAM,
        $disposition = Mime::DISPOSITION_ATTACHMENT,
        $encoding = Mime::ENCODING_BASE64,
        $filename = null
    ) {
        $this->parts[] = $this->createMimePart($body, $mimeType, $disposition, $encoding, $filename);
        return $this;
    }

    /**
     * @param stirng $content
     * @param string $type
     * @param string $disposition
     * @param string $encoding
     * @param null $filename
     * @return MimePart
     */
    private function createMimePart(
        $content,
        $type = Mime::TYPE_OCTETSTREAM,
        $disposition = Mime::DISPOSITION_ATTACHMENT,
        $encoding = Mime::ENCODING_BASE64,
        $filename = null
    ) {
        /** @var MimePart $mimePart */
        $mimePart = $this->mimePartFactory->create(['content' => $content]);
        $mimePart->setType($type);
        $mimePart->setDisposition($disposition);
        $mimePart->setEncoding($encoding);

        if ($filename) {
            $mimePart->setFileName($filename);
        }

        return $mimePart;
    }

    /**
     * @param MessageInterface $message
     * @return MimeMessage
     */
    private function getMimeMessage(MessageInterface $message)
    {
        $body = $message->getBody();

        if ($body instanceof MimeMessage) {
            return $body;
        }

        /** @var MimeMessage $mimeMessage */
        $mimeMessage = $this->mimeMessageFactory->create();

        if ($body) {
            $mimePart = $this->createMimePart((string)$body, Mime::TYPE_TEXT, Mime::DISPOSITION_INLINE);
            $mimeMessage->setParts([$mimePart]);
        }

        return $mimeMessage;
    }

    /**
     * @return $this
     */
    public function resetBuilder()
    {
        $this->parts = [];
        return $this;
    }
}
