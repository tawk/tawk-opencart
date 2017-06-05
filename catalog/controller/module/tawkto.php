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

		self::$displayed = TRUE;

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

		$this->render();
	}

	private function getWidget() {
		$this->load->model('setting/setting');

		$settings = $this->model_setting_setting->getSetting('tawkto_widget');

		$storeId = $this->config->get('config_store_id');
		$languageId = $this->config->get('config_language_id');
		$layoutId = $this->getLayoutId();

		$widget = null;

		if(isset($settings['widget_settings_for_'.$storeId])) {
			$widget = $settings['widget_settings_for_'.$storeId];
		}

		if(isset($settings['widget_settings_for_'.$storeId.'_'.$languageId])) {
			$widget = $settings['widget_settings_for_'.$storeId.'_'.$languageId];
		}

		if(isset($settings['widget_settings_for_'.$storeId.'_'.$languageId.'_'.$layoutId])) {
			$widget = $settings['widget_settings_for_'.$storeId.'_'.$languageId.'_'.$layoutId];
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