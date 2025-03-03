<?php
/**
* 2007-2025 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2025 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Perpetual extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'perpetual';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'islemDev';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();
        $this->registerHook('filterProductSearch');
        $this->displayName = $this->l('pertpetual tech test');
        $this->description = $this->l('perpetual technical test');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '8.0');
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('PERPETUAL_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('displayMediaBodyBefore') &&
            $this->registerHook('displaySubCategories') &&
            $this->registerHook('actionGetProductPropertiesAfter') &&
            $this->registerHook('filterProductSearch');
    }

    public function hookFilterProductSearch($params)
    {
    }

    public function hookActionGetProductPropertiesAfter($params)
    {
        $product = $params["product"];
        //some attributes have to change, ex: link
        if(!isset($params['product']["id_product_attribute"]))
            return;
        $params['product']["link"] = $this->context->link->getProductLink((int) $product['id_product'], $product['link_rewrite'], $product['category'], $product['ean13'], null, null, null, false, false, false, ["id_product_attribute" => $product["id_product_attribute"]]);
    }
    public function hookDisplaySubCategories($params)
    {
        /**
         * for the same reason as displaymediabeforebody + i'd prefer using hooks over overrides
         * in prestashop (especially early versions) the override is with single usage, so, let it as the last solution
         */

         $subCategories = $params["subCategories"];
         //add nb_products to subcategory
         $subCategories = array_map(function($subCategory) {
            $category = new Category($subCategory["id_category"]);
            $subCategory["nb_products"] = $category->getProducts($this->context->language->id, 1, 12, null, null, true);
            return $subCategory;
        }, $subCategories);


         //we can add caching, and cache refreshing hooks...
         $this->context->smarty->assign([
            "subCategories" => $subCategories
         ]);
         return $this->display(__FILE__, "display_sub_categories.tpl");

    }

    public function hookDisplayMediaBodyBefore($params)
    {

        /**
         * i choose to use a custom display hook over using actionCartPresent hook, for the ease of using smarty cache
         * this calculation would be performance killer if there is many category trees in the shop
         * those solution are oftenly discussed before implementation
         */
        $product = $params["product"];
        $id_product = $product["id"];
        $id_lang = $this->context->language->id;
        $id_category_default = $product["id_category_default"];
        $path = "module:{$this->name}/views/templates/hook/display_media_body_before.tpl";
        $cacheId = $this->getCacheId("{$this->name}:{$id_category_default}:{$id_product}:{$id_lang}");
        if(!$this->isCached($path, $cacheId)) {
            $this->context->smarty->assign([
                "category" => $this->getSubLevelCategory($id_category_default, $id_product, $id_lang)
            ]);
        }
        return $this->fetch($path);
        

    }

    private function getSubLevelCategory($id_category_default, $id_product, $id_lang)
    {
        $product = new Product($id_product);
        $productCategories = $product->getCategories();
        $category = new Category($id_category_default, $id_lang);
    
        $subCategories = $category->recurseLiteCategTree();
       
        $subLevelCategory = $id_category_default;

        array_walk_recursive($subCategories, function($item, $key) use($productCategories, &$subLevelCategory) {
            if("id" === $key && in_array($item, $productCategories)) 
                $subLevelCategory = $item;
        });

        
        return new Category($subLevelCategory, $id_lang);
    }

    public function uninstall()
    {
        Configuration::deleteByName('PERPETUAL_LIVE_MODE');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitPerpetualModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitPerpetualModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'PERPETUAL_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'PERPETUAL_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'PERPETUAL_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'PERPETUAL_LIVE_MODE' => Configuration::get('PERPETUAL_LIVE_MODE', true),
            'PERPETUAL_ACCOUNT_EMAIL' => Configuration::get('PERPETUAL_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'PERPETUAL_ACCOUNT_PASSWORD' => Configuration::get('PERPETUAL_ACCOUNT_PASSWORD', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        if($this->context->controller->php_self == "category") {
            $this->context->controller->addJS($this->_path.'/views/js/slick.min.js');
            $this->context->controller->addCSS($this->_path.'/views/css/slick-theme.css');
            $this->context->controller->addJS($this->_path.'/views/js/front.js');
            $this->context->controller->addCSS($this->_path.'/views/css/front.css');
        }
        
    }
}
