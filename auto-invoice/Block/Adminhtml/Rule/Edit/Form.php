<?php
declare(strict_types=1);

/**
 *
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
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AutoInvoice\Block\Adminhtml\Rule\Edit;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;

class Form extends Generic
{
    /**
     * @var \Magento\Rule\Block\Conditions
     */
    private $conditions;

    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    private $rendererFieldset;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Form constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param array $data
     */
    public function __construct(
        Context                                              $context,
        Registry                                             $registry,
        FormFactory                                          $formFactory,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Magento\Rule\Block\Conditions                       $conditions,
        array                                                $data = []
    ) {
        $this->storeManager = $context->getStoreManager();
        $this->rendererFieldset = $rendererFieldset;
        $this->conditions = $conditions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare Form
     * @return Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('partial_invoice_rule');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' =>
                [
                    'id' => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post'
                ]
            ]
        );
        $form->setHtmlIdPrefix('rule_');
        $form->setFieldNameSuffix('rule');
        $form->setUseContainer(true);

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General')]);

        $this->prepareBaseFieldSet($fieldset, $model);

        /**
         * Prepare for conditions fieldset
         */
        $renderer = $this->rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $this->getUrl('autoinv/rule/newConditionHtml/form/rule_conditions_fieldset')
        );

        $fieldset2 = $form->addFieldset(
            'conditions_fieldset',
            ['legend' => __(
                'Apply rule to product if the following conditions are met'
            )]
        )->setRenderer(
            $renderer
        );

        $fieldset2->addField(
            'conditions',
            'text',
            ['name' => 'conditions', 'label' => __('Conditions'), 'title' => __('Conditions')]
        )->setRule(
            $model
        )->setRenderer(
            $this->conditions
        );

        $data = $model->getData();
        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare Base Field Set for form
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param \Bss\AutoInvoice\Model\Rule $model
     */
    private function prepareBaseFieldSet(&$fieldset, $model)
    {
        if ($model->getId()) {
            $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }

        $fieldset->addField(
            'rule_name',
            'text',
            [
                'name' => 'rule_name',
                'label' => __('Rule Name'),
                'required' => true
            ]
        );
        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Active'),
                'values' => [
                    ['value' => 1, 'label' => __('Yes')],
                    ['value' => 0, 'label' => __('No')],
                ]
            ]
        );
        $fieldset->addField(
            'priority',
            'text',
            [
                'name' => 'priority',
                'label' => __('Priority'),
                'class' => 'validate-zero-or-greater'
            ]
        );

        $fieldset->addField(
            'store_id',
            'select',
            [
                'name' => 'store_id',
                'label' => __('Store View'),
                'title' => __('Store View'),
                'values' => $this->getStoreData()
            ]
        )->setCanBeEmpty(true);
    }

    /**
     * @return array
     */
    private function getStoreData()
    {
        $storeManagerDataList = $this->storeManager->getStores();
        $options = [];

        foreach ($storeManagerDataList as $key => $value) {
            $options[] = ['label' => $value['name'] . ' - ' . $value['code'], 'value' => $key];
        }
        return $options;
    }
}
