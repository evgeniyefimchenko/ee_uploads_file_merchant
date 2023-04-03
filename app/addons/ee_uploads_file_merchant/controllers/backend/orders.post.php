<?php
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'details') {
	$module_settings = Registry::get('addons.ee_uploads_file_merchant');
	$order_info = Tygh::$app['view']->getTemplateVars('order_info');
	if ($module_settings['ee_give_cp_attachments'] == 'N') {
		$order_info['ee_customer_url'] = db_get_field('SELECT ee_customer_url FROM ?:orders WHERE order_id = ?i', $order_info['order_id']);		
		if (strlen($order_info['ee_customer_url']) > 3) {				
			Tygh::$app['view']->assign('ee_orders_files', explode(';', $order_info['ee_customer_url']));
		}
		Tygh::$app['view']->assign('attachments', false);
	} else {
		if (function_exists('fn_get_attachments')) {			
			Tygh::$app['view']->assign('ee_orders_files', fn_get_attachments('orders', $order_info['order_id']));
			Tygh::$app['view']->assign('attachments', true);
		}
	}
}
