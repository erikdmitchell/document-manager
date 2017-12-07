jQuery( document ).ready(
    function($) {

        // upload file button //
        $( 'a#dm-metabox-file-upload' ).on(
            'click', function(e) {
                showLoader( '#wpwrap' );

                e.preventDefault();

                var formData = new FormData();
                var file     = document.getElementById( 'dm-metabox-file' );

                // action //
                formData.append( 'action', 'dm_metabox_upload_file' );

                // file //
                formData.append( 'file', file.files[0] );

                // nonce //
                formData.append( 'security', dmMetaboxOptions.nonce );

                // post id //
                formData.append( 'post_id', $( '#dm-metabox-post-id' ).val() );

                $.ajax(
                    {
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
                                reloadDocumentVersionMetaBox();
                            } else {
                                // alert(data.error);
                                reloadDocumentVersionMetaBox();
                            }

                            hideLoader();
                        }
                    }
                );
            }
        );

    }
);

function reloadDocumentVersionMetaBox() {
    console.log( 'ajax reload metabox' );

    var data = {
        'action': 'dm_reload_metabox',
        'post_id': jQuery( '#dm-metabox-post-id' ).val(),
        'metabox': 'Document_Manager_Document_Versions_Meta_Box',
    };

    jQuery.post(
        ajaxurl, data, function(response) {
            jQuery( '#dm-document-versions .inside' ).html( '' ); // clear
            jQuery( '#dm-document-versions .inside' ).html( response ); // add reloaded data
        }
    );
}

// create/display loader //
function showLoader(self) {
    var loaderContainer = jQuery(
        '<div/>', {
            'class': 'loader-image-container'
        }
    ).appendTo( self ).show();

    var loader = jQuery(
        '<img/>', {
            src: '/wp-admin/images/wpspin_light-2x.gif',
            'class': 'loader-image'
        }
    ).appendTo( loaderContainer );
}

// remove loader //
function hideLoader() {
    jQuery( '.loader-image-container' ).remove();
}
