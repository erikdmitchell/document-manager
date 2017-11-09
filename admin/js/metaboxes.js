jQuery(document).ready(function($) {

	// upload file button //
	$('a#dm-metabox-file-upload').on('click', function(e) {
		showLoader('#wpwrap');
		
		e.preventDefault();

		var formData = new FormData();
		var file=document.getElementById('dm-metabox-file');	
		
		// action //
		formData.append('action', 'dm_metabox_upload_file');
		
		// file //
		formData.append('file', file.files[0]);
				
		// nonce //
		formData.append('security', dmMetaboxOptions.nonce);		

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: formData,
			cache: false,
			dataType: 'json',
			processData: false, // dont process files
			contentType: false, // jquery will tell the server
			success: function(data, textStatus, jqXHR) {
				if (data.response == 'SUCCESS') {
					// todo
				} else {
					alert(data.error);
				}
				
				hideLoader();		
			}
		});
	});
	
});

// create/display loader //
function showLoader(self) {
	var loaderContainer = jQuery( '<div/>', {
		'class': 'loader-image-container'
	}).appendTo( self ).show();

	var loader = jQuery( '<img/>', {
		src: '/wp-admin/images/wpspin_light-2x.gif',
		'class': 'loader-image'
	}).appendTo( loaderContainer );
}

// remove loader //
function hideLoader() {
	jQuery('.loader-image-container').remove();
}