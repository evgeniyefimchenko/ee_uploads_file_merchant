{$extensions = $addons.ee_uploads_file_merchant.ee_select_file_types|array_keys}
{foreach $extensions as $k_e => $e_item}
	{$extensions.$k_e = ".`$e_item`"}
{/foreach}
{strip}
	<script>
		(function(_, $) {
			$.extend(_, {
				ee_uploads_file_merchant: {
					ee_type_payment: '{$addons.ee_uploads_file_merchant.ee_type_payment}',
					ee_select_file_types: '{", "|implode:$extensions}',
					ee_max_file_size: '{$addons.ee_uploads_file_merchant.ee_max_file_size}',
				}
			});
		}(Tygh, Tygh.$));
	</script>
{/strip}
{script src="js/addons/ee_uploads_file_merchant/script.js"}