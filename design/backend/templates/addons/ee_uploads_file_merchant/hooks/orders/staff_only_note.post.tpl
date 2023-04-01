{if $ee_orders_files}
	{__("ee_orders_files")}:<br/>
	{foreach from=$ee_orders_files item=item key=key}
		{$file=$item|pathinfo}
		<a style="font-size: 14px;" href="{$item}" download>{$file.filename}.{$file.extension} {__("Download")}</a><br/>
	{/foreach}
{/if}
