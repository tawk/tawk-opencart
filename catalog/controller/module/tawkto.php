<?php

/**
 * @package Tawk.to Integration
 * @author Tawk.to
 * @copyright (C) 2014- Tawk.to
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */


class ControllerModuleTawkto extends Controller {
    private static $displayed = false; //we include embed script only once

    protected function index() {

        if(self::$displayed) {
            return;
        }

        self::$displayed = true;

        $widget = $this->getWidget();

        if($widget === null) {
            echo '';
            return;
        }

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/tawkto.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/module/tawkto.tpl';
        } else {
            $this->template = 'default/template/module/tawkto.tpl';
        }

        $this->data['page_id'] = $widget['page_id'];
        $this->data['widget_id'] = $widget['widget_id'];
        $this->data['current_page'] = htmlspecialchars_decode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        $this->data['cart_data'] = array();
        $this->data['customer'] = array();
        $this->data['orders'] = array();

        $this->render();
    }

    private function getWidget() {
        $this->load->model('setting/setting');

        // get current store and load tawk.to options
        $store_id = $this->config->get('config_store_id');
        if (!$store_id || is_null($store_id)) {
            $store_id = 0;
        }

        $settings = $this->model_setting_setting->getSetting('tawkto_widget', $store_id);

        $storeId = $this->config->get('config_store_id');
        $languageId = $this->config->get('config_language_id');
        $layoutId = $this->getLayoutId();

        $widget = null;

        if(isset($settings['widget_config_'.$storeId])) {
            $widget = $settings['widget_config_'.$storeId];
        }

        if(isset($settings['widget_config_'.$storeId.'_'.$languageId])) {
            $widget = $settings['widget_config_'.$storeId.'_'.$languageId];
        }

        if(isset($settings['widget_config_'.$storeId.'_'.$languageId.'_'.$layoutId])) {
            $widget = $settings['widget_config_'.$storeId.'_'.$languageId.'_'.$layoutId];
        }

        // get visibility options
        $options = false;
        if (isset($settings['widget_visibility']))
            $options = $settings['widget_visibility'];

        if ($options) {
            $options = json_decode($options);

            // prepare visibility
            $request_uri = trim($_SERVER["REQUEST_URI"]);
            if (stripos($request_uri, '/')===0) {
                $request_uri = substr($request_uri, 1);
            }
            $current_page = $this->config->get('config_url').$request_uri;
            
            if (false == $options->always_display) {

                // custom pages
                $show_pages = json_decode($options->show_oncustom);
                if (!is_array($show_pages)) {
                    $show_pages = trim($show_pages);
                }
                $show = false;

                $current_page = (string) $current_page;
                foreach ($show_pages as $slug) {

                    if (empty(trim($slug))) {
                        continue;
                    }

                    $slug = (string) htmlspecialchars($slug); // we need to add htmlspecialchars due to slashes added when saving to database
                    $slug = str_ireplace($this->config->get('config_url'), '', $slug);
                    
                    // $slug = urlencode($slug);
                    if (stripos($current_page, $slug)!==false || trim($slug)==trim($current_page)) {
                        $show = true;
                        break;
                    }
                }

                // category page
                if (isset($this->request->get['route']) && stripos($this->request->get['route'], 'category')!==false) {
                    if (false != $options->show_oncategory) {
                        $show = true;
                    }
                }

                // home
                $is_home = false;
                if (!isset($this->request->get['route']) 
                    || (isset($this->request->get['route']) && $this->request->get['route'] == 'common/home')) {
                    $is_home = true;
                }
                
                if ($is_home) {
                    if (false != $options->show_onfrontpage) {
                        $show = true;
                    }                
                }

                
                
                

                if (!$show) {
                    return;
                }

            } else {
                $hide_pages = json_decode($options->hide_oncustom);
                $show = true;
                
                // $current_page = urlencode($current_page);
                $current_page = (string) $current_page;
                foreach ($hide_pages as $slug) {
                    $slug = (string) htmlspecialchars($slug); // we need to add htmlspecialchars due to slashes added when saving to database
                    $slug = str_ireplace($this->config->get('config_url'), '', $slug);

                    if (!empty($slug)) {
                        // $slug = urlencode($slug);
                        if (stripos($current_page, $slug)!==false || trim($slug)==trim($current_page)) {
                            $show = false;
                            break;
                        }
                    }
                }

                if (!$show) {
                    return;
                }
            }
        }

        return $widget;
    }

    private function getLayoutId() {
        if (isset($this->request->get['route'])) {
            $route = $this->request->get['route'];
        } else {
            $route = 'common/home';
        }

        $this->load->model('design/layout');

        return $this->model_design_layout->getLayout($route);
    }
}