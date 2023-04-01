<?php
if (!defined('BOOTSTRAP')) { die('Access denied'); }

use Tygh\Registry;

global $module_settings;
$module_settings = Registry::get('addons.ee_uploads_file_merchant');

function fn_ee_uploads_file_merchant_install() {
	$ee_customer_url = db_get_field('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME = "ee_customer_url"');
	if (!$ee_customer_url) {
		db_query('ALTER TABLE `?:orders` ADD COLUMN `ee_customer_url` TEXT NOT NULL DEFAULT ""');	
	}
}

function fn_ee_uploads_file_merchant_uninstall() {
	return true;
}

function fn_ee_uploads_file_merchant_place_order($order_id, $action, $order_status, $cart, $auth) {
	global $module_settings;
	$attachments_settings = Registry::get('addons.attachments');	
	$message = '';
	$temp_url = '';
	$all_files = [];
	$uploadFileDir = fn_get_files_dir_path() . 'ee_file_upload/';
	$allowedfileExtensions = array_keys($module_settings['ee_select_file_types']);
	if (!count($allowedfileExtensions)) {
		fn_set_notification('E', __('error'), __('ee_not_allowed_extensions'));
		return true;
	}
	if (!file_exists($uploadFileDir)) {
		mkdir($uploadFileDir, 0755, true);
	}
	if (!is_writable($uploadFileDir)) {
		$message .= 'Not writable ' . $uploadFileDir;
	}	
	if (isset($_FILES['file_customer_files']['name'])) {
		foreach ($_FILES['file_customer_files']['name'] as $key => $value) {
			if ($_FILES['file_customer_files']['error'][$key] == 0) {
				
				$fileTmpPath = $_FILES['file_customer_files']['tmp_name'][$key];
				$fileName = $_FILES['file_customer_files']['name'][$key];
				$fileSize = $_FILES['file_customer_files']['size'][$key];
				$fileType = $_FILES['file_customer_files']['type'][$key];

				if (round($fileSize / 1024 / 1024) > $module_settings['ee_max_file_size']) {
					$message .= ' ' . __('ee_file_is_too_large') . ' ' . $module_settings['ee_max_file_size'] . ' Mb';
				}
				
				$fileNameCmps = explode(".", $fileName);
				$fileExtension = strtolower(end($fileNameCmps));
				$newFileName = hash('ripemd160', time() . $fileName) . '.' . $fileExtension;								
				
				if (in_array($fileExtension, $allowedfileExtensions)) {					
					$dest_path = $uploadFileDir . $newFileName;		 
					if (move_uploaded_file($fileTmpPath, $dest_path)) {
						chmod($dest_path, 0755);						
						$all_files[] = array('dest_path' => $dest_path, 'fileName' => $fileName, '');
					}
					else {
						$message .= ' Error, path: from ' . $fileTmpPath . ' to ' . $dest_path;
					}
				} else {
					$message .= 'The extension not allowed: ' . $fileExtension;
				}
			}
		}
		// ô Ø·ãñë ¨Ò ª ½Ðñ ¨ãÐ· Ô¨å Öõ·¢ÖÆ 		
		if (mb_strlen($message) > 0) {
			fn_set_notification('E', __('error'), trim($message));
		} else {					
			if ($module_settings['ee_give_archive'] == 'Y' && class_exists('ZipArchive')) {
				$dest_path = $uploadFileDir . $order_id . '.zip';
				$zip = new ZipArchive();
				$zip->open($dest_path, ZIPARCHIVE::CREATE);		
				foreach ($all_files as $file) { 							
					$zip->addFile($file['dest_path'], $file['fileName']);												
				}
				$zip->close();
				$pre_temp_url = (defined('HTTPS') ? 'https://' : 'http://') . REAL_HOST . '/' . stristr($dest_path, 'var/files/');				
				foreach ($all_files as $file) {
					unlink($file['dest_path']);
				}
				if ($module_settings['ee_give_cp_attachments'] == 'Y' && $attachments_settings && $attachments_settings['status'] == 'A') {
					$file_info = pathinfo($dest_path);
					fn_update_attachments(
						['description' => 'checkout', 'position' => 666, 'usergroup_ids' => 0, 'on_server' => 'Y'],
						0,
						'orders',
						$order_id,
						'M',
						['name' => $file_info['filename'], 'url' => $pre_temp_url, 'path' => $file_info['dirname'] . '/' . $file_info['basename'], 'size' => filesize($file_info['dirname'] . '/' . $file_info['basename'])],
						DESCR_SL
					);
				} else {
					db_query('UPDATE ?:orders SET ee_customer_url = ?s WHERE order_id = ?i', $pre_temp_url . ';', $order_id);
				}
			} else {
				foreach ($all_files as $file) {
					$dest_path = $file['dest_path'];
					$pre_temp_url = (defined('HTTPS') ? 'https://' : 'http://') . REAL_HOST . '/' . stristr($dest_path, 'var/files/');					
					$arr_files[] = $pre_temp_url;
				}
				if ($module_settings['ee_give_cp_attachments'] == 'Y' && $attachments_settings && $attachments_settings['status'] == 'A') {
					foreach ($arr_files as $item) {
						$file_info = pathinfo($item);
						fn_update_attachments(
							['description' => 'checkout', 'position' => 666, 'usergroup_ids' => 0, 'on_server' => 'Y'],
							0,
							'orders',
							$order_id,
							'M',
							['name' => $file_info['filename'], 'url' => $item, 'path' => $file_info['dirname'] . '/' . $file_info['basename'], 'size' => filesize($file_info['dirname'] . '/' . $file_info['basename'])],
							DESCR_SL
						);
					}
				} else {
					db_query('UPDATE ?:orders SET ee_customer_url = ?s WHERE order_id = ?i', implode(';', $arr_files), $order_id);
				}
			}
		}		
	}
}
