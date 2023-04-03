<?php
if (isset($_GET['file']) && !is_numeric($_GET['file'])) {
	fn_ee_file_force_download(base64_decode($_GET['file']));
} else if ($_GET['file']) {
	fn_get_attachment($_GET['file']);
}
exit;
