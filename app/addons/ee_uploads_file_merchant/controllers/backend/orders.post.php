<?php
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'details') {
	$module_settings = Registry::get('addons.ee_uploads_file_merchant');
	$order_info = Tygh::$app['view']->getTemplateVars('order_info');
	if ($module_settings['ee_give_cp_attachments'] == 'N') {
		$order_info['ee_customer_url'] = db_get_field('SELECT ee_customer_url FROM ?:orders WHERE order_id = ?i', $order_info['order_id']);
		if (mb_strlen($order_info['ee_customer_url']) > 3) {
			$order_info['ee_customer_url'] = explode(';', $order_info['ee_customer_url']);
			array_pop($order_info['ee_customer_url']);		
			Tygh::$app['view']->assign('ee_orders_files', $order_info['ee_customer_url']);
		}
	} else {
		if (function_exists('fn_get_attachments')) {
			fn_print_r(fn_get_attachments('order', $order_info['order_id']));
		}
	}
}
