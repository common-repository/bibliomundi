; jQuery( function( $ ) {
	
	var timeout;

	function pollingImportStatus() {
		var data = {'action' : 'bibliomundi_import_status'};
		$.post( ajaxurl, data, function(d) {console.log(d);
			switch(d.status) {
				case 'progress' :
					if(d.total) {
						var percent = Number((parseInt(d.current) / parseInt(d.total) * 100).toFixed(0));
						$('.bibliomundi-alert').html("Importing ... "+percent+"%").addClass('updated').show();
					}
					setTimeout( function() {
						pollingImportStatus();
					}, 1000 );
					break;
				case 'complete' :
					// do nothing
					break;
				default :
					$('.bibliomundi-alert').html('Unexpected error occurs').addClass('error').show();
					setTimeout( function() {
						$('.bibliomundi-alert').hide().empty();
					}, 5000 );
					break;
			}
		}, 'json');
	}

	$( '.bibliomundi-button' ).click( function() {
		var _this   = $( this );
		var buttons = $( '.bibliomundi-button' );
		var nonce   = $( '#bbm-nonce' ).val();
		var scope = '';
		if (_this.hasClass( 'remove' )) {
			scope = 'remove';
		} else {
            scope =_this.hasClass( 'complete' ) ? 'complete' : 'updates';
		}
		var action = (scope === 'remove') ? 'bibliomundi_remove_products' : 'bibliomundi_import_catalog';
		var alert   = _this.parent().find('.bibliomundi-alert');
		var confirm = (scope === 'remove') ? window.confirm('Are you sure?') : true;
		if (confirm) {
            if (!buttons.hasClass('disabled')) {
                buttons.removeClass('loading')
                    .addClass('disabled');

                _this.addClass('loading');

                clearTimeout(timeout);
                alert.removeClass('error updated').empty();

                var data = {
                    'action': action,
                    'security': nonce,
                    'scope': scope
                };


                $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: data,
                    dataType: 'json'
                }).done(function (d) {
                    _this.removeClass('loading');
                    buttons.removeClass('disabled');

                    alert.text(d.msg).addClass(d.error ? 'error' : 'updated').show();
                    timeout = setTimeout(function () {
                        alert.hide().empty();
                    }, 5000);
                });

                if (scope !== 'remove') {
                    setTimeout(function () {
                        pollingImportStatus();
                    }, 2000);
                } else {
                    alert('Delete complete');
                }

            }
        }
		return false;
	} );

} );