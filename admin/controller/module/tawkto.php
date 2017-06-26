<?php

/**
 * @package Tawk.to Integration
 * @author Tawk.to
 * @copyright (C) 2014- Tawk.to
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */


class ControllerModuleTawkto extends Controller {
    private $error = array();

    private function setup() {
        $this->language->load('module/tawkto');
        $this->load->model('setting/setting');
        $this->load->model('design/layout');
        $this->load->model('setting/store');
        $this->load->model('localisation/language');

        //just so we would intercept every request every time
        $this->enableAllLayouts();
    }

    public function index() {

        $this->setup();

        $this->setupIndexTexts();

        // get current store and load tawk.to options
        $store_id = 0;
        $stores = $this->model_setting_store->getStores();
        if (!empty($stores)) {
            foreach ($stores as $store) {
                if ($this->config->get('config_url') == $store['url']) {
                    $store_id = intval($store['store_id']);
                }
            }
        }

        $this->data['base_url']   = $this->getBaseUrl();
        $this->data['iframe_url'] = $this->getIframeUrl();
        $this->data['hierarchy']  = $this->getStoreHierarchy($store_id);
        $this->data['widget_config']  = $this->getWidgetConfig($store_id);
        $this->data['display_opts']  = $this->getDisplayOptions($store_id);
        $this->data['store_id']  = $store_id;
        $this->data['store_layout_id']  = $store_id;

        $this->template = 'module/tawkto.tpl';
        $this->children = array(
                'common/header',
                'common/footer'
            );

        $this->response->setOutput($this->render());
    }

    public function getWidgetConfig($store_id) {
        $current_settings = $this->model_setting_setting->getSetting('tawkto_widget', $store_id);

        $config = array(
                'page_id' => null,
                'widget_id' => null
            );
        
        if (isset($current_settings['widget_config_'.$store_id])) {
            $config = $current_settings['widget_config_'.$store_id];
        }
        
        return $config;
    }

    public function getDisplayOptions($store_id) {
        $current_settings = $this->model_setting_setting->getSetting('tawkto_widget', $store_id);

        $options = array(
                'always_display' => true,
                'hide_oncustom' => array(),
                'show_onfrontpage' => false,
                'show_oncategory' => false,
                'show_oncustom' => array()
            );
        if (isset($current_settings['widget_visibility'])) {
            $options = $current_settings['widget_visibility'];
            $options = json_decode($options,true);
        }
        
        return $options;
    }

    private function validatePost() {
        return !empty($_POST['page_id']) && !empty($_POST['widget_id']) && isset($_POST['store'])
            && preg_match('/^[0-9A-Fa-f]{24}$/', $_POST['page_id']) === 1
            && preg_match('/^[a-z0-9]{1,50}$/i', $_POST['widget_id']) === 1;
    }

    public function setoptions()
    {
        header('Content-Type: application/json');

        $jsonOpts = array(
                'always_display' => false,
                'hide_oncustom' => array(),
                'show_onfrontpage' => false,
                'show_oncategory' => false,
                'show_onproduct' => false,
                'show_oncustom' => array(),
            );

        if (isset($_REQUEST['options']) && !empty($_REQUEST['options'])) {
            // $_REQUEST['options'] = urldecode($_REQUEST['options']);
            $options = explode('&', $_REQUEST['options']);

            foreach ($options as $post) {
                list($column, $value) = explode('=', $post);
                $column = str_ireplace('amp;', '', $column);
                switch ($column) {
                    case 'hide_oncustom':
                    case 'show_oncustom':
                        // replace newlines and returns with comma, and convert to array for saving
                        $value = urldecode($value);
                        $value = str_ireplace(["\r\n", "\r", "\n"], ',', $value);
                        $value = explode(",", $value);
                        $value = (empty($value)||!$value)?array():$value;
                        $jsonOpts[$column] = json_encode($value);
                        break;
                    
                    case 'show_onfrontpage':
                    case 'show_oncategory':
                    case 'show_onproduct':
                    case 'always_display':
                    // default:
                        $jsonOpts[$column] = ($value==1)?true:false;
                        break;
                }
            }
        }

        $this->setup();
        $store_id = intval($_POST['store']);
        $current_settings = $this->model_setting_setting->getSetting('tawkto_widget', $store_id);
        $current_settings['widget_visibility'] = json_encode($jsonOpts);

        $this->model_setting_setting->editSetting('tawkto_widget', $current_settings, $store_id);

        echo json_encode(array('success' => true));
        die();
    }

    public function setwidget() {
        header('Content-Type: application/json');

        if(!$this->validatePost()) {
            echo json_encode(array('success' => false));
            die();
        }

        $this->setup();

        $store_id = intval($_POST['store']);
        $store_layout_id = trim($_POST['store_layout']);

        $current_settings = $this->model_setting_setting->getSetting('tawkto_widget', $store_id);

        $current_settings['widget_config_'.$store_layout_id] = array(
            'page_id' => trim($_POST['page_id']),
            'widget_id' => trim($_POST['widget_id'])
        );

        $this->model_setting_setting->editSetting('tawkto_widget', $current_settings, $store_id);

        echo json_encode(array('success' => true));
        die();
    }

    public function removewidget() {
        header('Content-Type: application/json');

        if(!isset($_POST['store'])) {
            echo json_encode(array('success' => false));
            die();
        }

        $this->setup();

        $store_id = intval($_POST['id']);
        $store_layout_id = trim($_POST['store_layout']);
        $current_settings = $this->model_setting_setting->getSetting('tawkto_widget', $store_id);
        
        unset($current_settings['widget_config_'.$store_layout_id]);

        $this->model_setting_setting->editSetting('tawkto_widget', $current_settings, $store_id);

        echo json_encode(array('success' => true));
        die();
    }

    private function setupIndexTexts() {
        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
                'text'      => $this->language->get('text_home'),
                'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
                'separator' => false
            );

        $this->data['breadcrumbs'][] = array(
                'text'      => $this->language->get('text_module'),
                'href'      => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),
                'separator' => ' :: '
            );

        $this->data['breadcrumbs'][] = array(
                'text'      => $this->language->get('heading_title'),
                'href'      => $this->url->link('module/tawkto', 'token=' . $this->session->data['token'], 'SSL'),
                'separator' => ' :: '
            );

        $this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['button_cancel'] = $this->language->get('button_cancel');
        $this->data['text_installed'] = $this->language->get('text_installed');
    }

    private function getStoreHierarchy($sid) {
        $sid = intval($sid);
        $this->layouts = $this->model_design_layout->getLayouts();
        $this->languages = $this->model_localisation_language->getLanguages();
        $this->currentSettings = $this->model_setting_setting->getSetting('tawkto_widget', $sid);

        $hierarchy = array();

        $hierarchy[] = array(
            'id'      => '0',
            'name'    => 'Default store',
            'current' => $this->getCurrentSettingsFor('0'),
            'childs'  => $this->getLanguageHierarchy('0')
        );

        $stores = $this->model_setting_store->getStores();
        foreach($stores as $store) {
            $hierarchy[] = array(
                'id'      => $store['store_id'],
                'name'    => $store['name'],
                'current' => $this->getCurrentSettingsFor($store['store_id']),
                'childs'  => $this->getLanguageHierarchy($store['store_id'])
            );
        }

        return $hierarchy;
    }

    private function getLanguageHierarchy($parent) {
        $return = array();

        foreach($this->languages as $code => $details) {
            $return[] = array(
                    'id'      => $parent . '_' . $details['language_id'],
                    'name'    => $details['name'],
                    'current' => $this->getCurrentSettingsFor($parent.'_'.$details['language_id']),
                    'childs'  => $this->getLayoutHierarchy($parent.'_'.$details['language_id'])
                );
        }

        return $return;
    }

    private function getLayoutHierarchy($parent) {
        $return = array();

        foreach ($this->layouts as $layout) {
            $return[] = array(
                    'id'      => $parent . '_' . $layout['layout_id'],
                    'name'    => $layout['name'],
                    'childs'  => array(),
                    'current' => $this->getCurrentSettingsFor($parent.'_'.$layout['layout_id'])
                );
        }

        return $return;
    }

    private function getCurrentSettingsFor($id) {
        if(isset($this->currentSettings['widget_settings_for_'.$id])) {
            $settings = $this->currentSettings['widget_settings_for_'.$id];

            return array(
                'pageId' => $settings['page_id'],
                'widgetId' => $settings['widget_id']
            );
        } else {
            return array();
        }
    }

    private function getIframeUrl() {
        $settings = $this->model_setting_setting->getSetting('tawkto');

        return $this->getBaseUrl()
            .'/generic/widgets/'
            .'?selectType=singleIdSelect'
            .'&selectText=Store';
    }

    private function getBaseUrl() {
        return 'https://plugins.tawk.to';
    }

    public function install() {
        $this->setup();
        $this->enableAllLayouts();
    }

    public function uninstall() {
        $this->setup();

        $this->model_setting_setting->deleteSetting('tawkto');
        $this->model_setting_setting->deleteSetting('tawkto_widget');
    }

    private function enableAllLayouts() {

        $settings = array();
        $settings['tawkto_module'] = array();

        $layouts = $this->model_design_layout->getLayouts();

        foreach ($layouts as $layout) { //will enable our module in every page
            $settings['tawkto_module'][] = Array (
                'layout_id'  => $layout['layout_id'],
                'position'   => 'content_bottom',
                'status'     => 1,
                'sort_order' => ''
            );
        }

        $this->model_setting_setting->editSetting('tawkto', $settings);
    }
}