<?php

/**
 * 2016 V-Coders
 *
 * @author    V-Coders <dudnik1986@gmail.com>
 * @copyright 2016 V-Coders
 * @license   http://www.gnu.org/philosophy/categories.html (Shareware)
 */
class VCXmlGetContentController
{
    public function __construct($module, $file, $path)
    {
        $this->file = $file;
        $this->module = $module;
        $this->context = Context::getContext();
        $this->_path = $path;
    }

    public function run()
    {
        $this->processConfiguration();
        if ($this->context->cookie->__isset('confirmation')) {
            $this->context->smarty->assign('confirmation', $this->context->cookie->__get('confirmation'));
            $this->context->cookie->__unset('confirmation');
        }
        return $this->module->display($this->file, 'getContent.tpl') . $this->renderForm();
    }

    private function renderForm()
    {
        $link = file_exists(_PS_MODULE_DIR_ . $this->module->name . "/import/feed.xml") ?
            $this->context->link->getMediaLink(_MODULE_DIR_ . $this->module->name . "/import/feed.xml") :
            '';


        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->module->language['text_configuration'],
                    'icon' => 'icon-envelope'
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->module->language['text_generation'],
                        'name' => 'generation',
                        'values' => array(
                            array('id' => 'generation_1', 'value' => 1, 'label' => $this->module->language['text_yes']),
                            array('id' => 'generation_0', 'value' => 0, 'label' => $this->module->language['text_no'])
                        ),
                    ),
                ),

                'submit' => array('title' => $this->module->language['text_save']),
            )
        );

        if($link){
            $fields_form['form']['input'][] = array(
                'type' => 'text',
                'label' => $this->module->language['link'],
                'name' => 'link'
                );
        }

        $helper = new HelperForm();
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int)Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->submit_action = $this->module->name . '_submit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false);
        $helper->currentIndex .= '&configure=';
        $helper->currentIndex .= $this->module->name;
        $helper->currentIndex .= '&tab_module=';
        $helper->currentIndex .= $this->module->tab;
        $helper->currentIndex .= '&module_name=';
        $helper->currentIndex .= $this->module->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => array(
                'generation' => Tools::getValue('generation', false),
            ),
            'languages' => $this->context->controller->getLanguages()
        );
        if($link){
            $helper->tpl_vars['fields_value']['link'] = $link;
        }
        return $helper->generateForm(array($fields_form));
    }

    private function processConfiguration()
    {
        if (Tools::isSubmit($this->module->name . '_submit')) {
            $generation = (int)Tools::getValue('generation');
            if ($generation) {
                $this->processGeneration();
                $this->context->cookie->__set('confirmation', $this->module->language['success_generation']);
            }
            Tools::redirectAdmin(
                $this->context->link->getAdminLink('AdminModules') .
                '&configure=' . $this->module->name .
                '&tab_module=' . $this->module->tab .
                '&module_name=' . $this->module->name
            );
        }
    }

    private function processGeneration()
    {
        $dom = new domDocument("1.0", "utf-8");
        //$root = $dom->createElement("price");
        //$root->setAttribute("date", date('Y-m-d H:i'));
        //$dom->appendChild($root);

        // Shop name
        //$name = $dom->createElement("name", Configuration::get('PS_SHOP_NAME'));
        //$root->appendChild($name);

        // Shop url
        //$url = $dom->createElement("url", $this->context->link->getPageLink('index', null, null, null, false, null, false));
        //$root->appendChild($url);

        //Products
        $items = $dom->createElement("items");

        $products = Product::getProducts($this->context->language->id, 0, 0, 'id_product', 'ASC');
        foreach ($products as $product) {
		$item = $dom->createElement("item");
        //id
            $id = $dom->createElement("id", $product['id_product']);
            $item->appendChild($id);
		//title
			$name = $dom->createTextNode($product['name']);
			$manuf = $dom->createTextNode(Manufacturer::getNameById($product['id_manufacturer']));
			$ris = $dom->createTextNode (" - ");
			$title = $dom->createElement('title');
			$title->appendChild($manuf);
			$title->appendChild($ris);
			$title->appendChild($name);
			$item->appendChild($title);
			
		//description
            $description_text = $dom->createTextNode($product['name']);
            $manuf = $dom->createTextNode(Manufacturer::getNameById($product['id_manufacturer']));
            $ris = $dom->createTextNode (" - ");
            $description = $dom->createElement('description');
            $description->appendChild($manuf);
            $description->appendChild($ris);
            $description->appendChild($description_text);
            $item->appendChild($description);

        //url
            $url = $dom->createElement("url", $this->context->link->getProductLink($product));
            $item->appendChild($url);

        //image
            $cover = Product::getCover($product['id_product']);
            $image = $dom->createElement("image_link", $this->context->link->getImageLink($product['link_rewrite'], $cover['id_image']));
            $item->appendChild($image);

        //price
            $val = $dom->createTextNode(Tools::convertPriceFull(
                    Product::getPriceStatic($product['id_product']),
                    null,
                    new Currency(Currency::getIdByIsoCode('UAH'))
                )
            );
            $cur_name = $dom->createTextNode(" UAH");
            $price = $dom->createElement("price");
            $price->appendChild($val);
            $price->appendChild($cur_name);
            $item->appendChild($price);

            $items->appendChild($item);
        }

        $dom->appendChild($items);
        $dom->save(_PS_MODULE_DIR_ . $this->module->name . "/import/feed.xml");
    }
}