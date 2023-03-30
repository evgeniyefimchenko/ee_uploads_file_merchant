{if $ee_orders_files}
	{__("ee_orders_files")}:<br/>
	{foreach from=$ee_orders_files item=item key=key}
		<a style="font-size: 14px;" href="{$item}" download>{__("Download")}</a><br/>
	{/foreach}
{/if}
