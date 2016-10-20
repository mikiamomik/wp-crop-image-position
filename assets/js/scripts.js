jQuery(document).ready(function() {

    jQuery(document).on('click', '.crop--image--position .button', function(e) {

        var $this = jQuery(this),
            data = {
                action: 'cip',
                _wpnonce: cipL10n._wpnonce,
                cip_position_option: parseInt($this.data('cip'), 10)
            };
        $this.addClass('loading');
        jQuery('.crop--image--position .button').addClass('button-not-enabled');

        jQuery.post(ajaxurl, data).done(function(response) {
            response = parseInt(response, 10);
            if (response < 9 && response >= 0) {
                jQuery('.crop--image--position').find('.button')
									.removeClass('button-primary')
									.removeClass('button-not-enabled');
                $this.addClass('button-primary');
            }
        }).always(function() {
            $this.removeClass('loading');
            jQuery('.crop--image--position').find('.button')
							.removeClass('button-not-enabled');
        });

    }).removeAttr('disabled');

});
