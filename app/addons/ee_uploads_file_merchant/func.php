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

function fn_ee_file_force_download($file) {
  if (file_exists($file)) {
    if (ob_get_level()) {
      ob_end_clean();
    }
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . basename($file));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    if ($fd = fopen($file, 'rb')) {
      while (!feof($fd)) {
        print fread($fd, 1024);
      }
      fclose($fd);
    } else {
		fn_print_die('file crash ' . $file);
	}
    exit;
  }
}

function fn_ee_uploads_file_merchant_place_order($order_id, $action, $order_status, $cart, $auth) {
	global $module_settings;
	$attachments_settings = Registry::get('addons.attachments');	
	$message = '';
	$all_files = [];
	$uploadFileDir = fn_get_files_dir_path() . 'ee_file_upload/';
	$allowedfileExtensions = array_keys($module_settings['ee_select_file_types']);
	if (!count($allowedfileExtensions)) {
		fn_set_notification('E', __('error'), __('ee_not_allowed_extensions'));
		return true;
	}
	if (!file_exists($uploadFileDir)) {
		mkdir($uploadFileDir, 0777, true);
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
						chmod($dest_path, 0777);						
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
				if ($module_settings['ee_give_cp_attachments'] == 'Y' && $attachments_settings && $attachments_settings['status'] == 'A') {
					$file_info = pathinfo($dest_path);					
					fn_update_attachments (
						['description' => 'Order ' . $order_id, 'position' => 666, 'usergroup_ids' => 0, 'on_server' => 'Y'],
						0,
						'orders',
						$order_id,
						'M',
						['name' => $file_info['basename'], 'url' => '', 'path' => $dest_path, 'size' => filesize($dest_path)],
						DESCR_SL
					);
					$dest_path = DIR_ROOT . '/var/attachments/orders/' . $order_id . '/' . $file_info['basename'];
				}
				db_query('UPDATE ?:orders SET ee_customer_url = ?s WHERE order_id = ?i', $dest_path, $order_id);
			} else {
				if ($module_settings['ee_give_cp_attachments'] == 'Y' && $attachments_settings && $attachments_settings['status'] == 'A') {
					foreach ($all_files as $item) {
						$attachment_id = fn_update_attachments (
							['description' => 'Order ' . $order_id, 'position' => 666, 'usergroup_ids' => 0, 'on_server' => 'Y'],
							0,
							'orders',
							$order_id,
							'M',
							['name' => $item['fileName'], 'url' => '', 'path' => $item['dest_path'], 'size' => filesize($item['dest_path'])],
							DESCR_SL
						);
						$new_path_file = DIR_ROOT . '/var/attachments/orders/' . $order_id . '/' . $item['fileName'];
						foreach ($all_files as $item_temp) {	
							if ($item_temp['dest_path'] == $item['dest_path']) {
								$res[] = $new_path_file;
							}
						}
					}
					$all_files = $res;					
				}
				db_query('UPDATE ?:orders SET ee_customer_url = ?s WHERE order_id = ?i', implode(';', $all_files), $order_id);
			}
		}		
	}
}
