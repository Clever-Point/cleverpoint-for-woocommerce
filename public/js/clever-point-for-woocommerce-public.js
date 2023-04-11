(function( $ ) {
	'use strict';
	$(document).ready(function(){
		$(document).on('click','#cleverpoint-modal-validate-trigger',function (e){
			e.preventDefault();
			setTimeout(function(){
				MicroModal.show('modal-1');
			}, 500);
		});

		$(document).on('click','.clever-point-map-opener',function (e){
			e.preventDefault();
			setTimeout(function(){
				MicroModal.show('modal-1');
			}, 500);
		});

		$(document).on('click','.clever-point-what-opener',function (e){
			e.preventDefault();
			setTimeout(function(){
				MicroModal.show('what_is_cleverpoint');
			}, 500);
		});

		$( 'form.checkout' ).on( 'change', 'input[name^="shipping_method"]', function() {
			if (this.value === 'clever_point_shipping_class') {
				$('.clevermap-container').show();
				if (jQuery('#cleverpoint-modal-trigger').length) {
					setTimeout(function(){
						MicroModal.show('modal-1');
					}, 500);
				}
			}else{
				$('.clevermap-container').hide();
			}
		});
	});
})( jQuery );