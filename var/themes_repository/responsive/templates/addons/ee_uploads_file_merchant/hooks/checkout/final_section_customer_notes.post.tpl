{if $payment_method.payment_id == $addons.ee_uploads_file_merchant.ee_type_payment}
	<div class="ty-product-options__elem ty-product-options__fileuploader" style="margin-left: 10px; cursor: pointer;">
		{$max_upload_filesize = "`$addons.ee_uploads_file_merchant.ee_max_file_size`M"}
		{include file="common/fileuploader.tpl" var_name="customer_files[]" multiupload=$addons.ee_uploads_file_merchant.ee_give_multiloads max_upload_filesize=$max_upload_filesize}
	</div>
{/if}
