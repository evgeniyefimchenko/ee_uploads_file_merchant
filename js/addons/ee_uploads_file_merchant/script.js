(function ( _, $ ) {
	$.ceEvent('on', 'ce.commoninit', function (context) {
		$('#litecheckout_payments_form').attr('enctype', 'multipart/form-data');
		$('.ty-fileuploader__file-local.upload-file-local').attr({'title' : Tygh.ee_uploads_file_merchant.ee_select_file_types}).addClass('cm-tooltip');
		$('.ty-fileuploader__file-input').attr({'accept' : Tygh.ee_uploads_file_merchant.ee_select_file_types});
	});
})(Tygh, Tygh.$);