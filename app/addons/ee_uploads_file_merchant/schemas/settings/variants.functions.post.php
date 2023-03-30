<?php
if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_settings_variants_addons_ee_uploads_file_merchant_ee_type_payment() {
	$payments_data = fn_get_payments(['lang_code' => 'ru']);
	$res = [];
	if (is_array($payments_data)) {
		foreach ($payments_data as $k => $v) {
			if ($v['status'] == 'A') $res[$k] = $v['payment'];
		}
	}
	return $res;
}
