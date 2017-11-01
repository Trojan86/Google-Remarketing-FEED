<?php
/**
 * 2016 V-Coders
 *
 * @author    V-Coders <dudnik1986@gmail.com>
 * @copyright 2016 V-Coders
 * @license   http://www.gnu.org/philosophy/categories.html (Shareware)
 */


class VCXml extends Module {
    public $language = array();

    public function __construct()
    {
        $this->name = 'vcxml';
        $this->tab = 'other';
        $this->version = '0.1';
        $this->author = 'Vladimir Dudnik';
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('V-Coders XML Export');
        $this->description = $this->l('Export for Adwords');
        $this->initLanguage();
    }
    public function initLanguage()
    {
        if(!$this->language)
        $this->language = array(
            'success_generation' => $this->l('Success Generation'),
            'text_generation' => $this->l('Generation new feed for Adwords'),
            'text_configuration' => $this->l('V-Coders XML Export configuration'),
            'text_yes' => $this->l('Yes'),
            'text_no' => $this->l('No'),
            'text_save' => $this->l('Save'),
            'link' => $this->l('Link'),
        );
    }

    public function getHookController($hook_name)
    {
        require_once(dirname(__FILE__) . '/controllers/hook/' . $hook_name . '.php');
        $controller_name = $this->name . $hook_name . 'Controller';
        $controller = new $controller_name($this, __FILE__, $this->_path);
        return $controller;
    }

    public function getContent()
    {
        $controller = $this->getHookController('getContent');
        return $controller->run();
    }

}