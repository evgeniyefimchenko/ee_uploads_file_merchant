{if $addons.ee_uploads_file_merchant.ee_show_list == 'Y' && $ee_orders_files}
	{$counter = 0}
	<h4 class="subheader">{__("ee_orders_files")}:</h4>
	{foreach from=$ee_orders_files item=item key=key}
		{$counter = $counter + 1}
		{if $attachments}
			<b>№{$counter}</b><a style="font-size: 14px; padding: 0 15px;" target="_blank" class="cm-tooltip"
			title="{__("Download")}" href="{'trigger_ee_uploads_file_merchant.php&file='|fn_url}{$item.attachment_id}" download>{$item.filename}</a><br/>			
		{else}
			{$file=$item|pathinfo}
			<b>№{$counter}</b><a style="font-size: 14px; padding: 0 15px;" target="_blank" class="cm-tooltip"
			title="{__("Download")}" href="{'trigger_ee_uploads_file_merchant.php&file='|fn_url}{$item|base64_encode}" download>{$file.basename}</a><br/>
		{/if}
	{/foreach}
	<hr/>
{/if}
