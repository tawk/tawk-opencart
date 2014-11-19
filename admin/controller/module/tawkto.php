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

		$this->data['base_url']   = $this->getBaseUrl();
		$this->data['iframe_url'] = $this->getIframeUrl();
		$this->data['hierarchy']  = $this->getStoreHierarchy();

		$this->template = 'module/tawkto.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	private function idsAreCorrect() {
		return !empty($_POST['pageId']) && !empty($_POST['widgetId']) && isset($_POST['id'])
			&& preg_match('/^[0-9A-Fa-f]{24}$/', $_POST['pageId']) === 1
			&& preg_match('/^[a-z0-9]{1,50}$/i', $_POST['widgetId']) === 1;
	}

	public function setwidget() {
		header('Content-Type: application/json');

		if(!$this->idsAreCorrect()) {
			echo json_encode(array('success' => FALSE));
			die();
		}

		$this->setup();

		$currentSettings = $this->model_setting_setting->getSetting('tawkto_widget');

		$currentSettings['widget_settings_for_'.$_POST['id']] = array(
			'page_id' => $_POST['pageId'],
			'widget_id' => $_POST['widgetId']
		);

		$this->model_setting_setting->editSetting('tawkto_widget', $currentSettings);

		echo json_encode(array('success' => TRUE));
		die();
	}

	public function removewidget() {
		header('Content-Type: application/json');

		if(!isset($_POST['id'])) {
			echo json_encode(array('success' => FALSE));
			die();
		}

		$this->setup();

		$currentSettings = $this->model_setting_setting->getSetting('tawkto_widget');

		unset($currentSettings['widget_settings_for_'.$_POST['id']]);

		$this->model_setting_setting->editSetting('tawkto_widget', $currentSettings);

		echo json_encode(array('success' => TRUE));
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

	private function getStoreHierarchy() {
		$stores = $this->model_setting_store->getStores();
		$this->layouts = $this->model_design_layout->getLayouts();
		$this->languages = $this->model_localisation_language->getLanguages();
		$this->currentSettings = $this->model_setting_setting->getSetting('tawkto_widget');

		$hierarchy = array();

		$hierarchy[] = array(
			'id'      => '0',
			'name'    => 'Default store',
			'current' => $this->getCurrentSettingsFor('0'),
			'childs'  => $this->getLanguageHierarchy('0')
		);

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