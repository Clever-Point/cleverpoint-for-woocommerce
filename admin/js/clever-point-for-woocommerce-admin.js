(function( $ ) {
	'use strict';
	jQuery(document).ready(function(){
		if (jQuery('input[name="clever_point_categories_not_to_list_invert"]').length>0) {
			jQuery('input[name="clever_point_categories_not_to_list_invert"]').parents('tr').hide();
		}
		if (jQuery('input[name="clever_point_tags_not_to_list_invert"]').length>0) {
			jQuery('input[name="clever_point_tags_not_to_list_invert"]').parents('tr').hide();
		}
		if (jQuery('input[name="clever_point_shipping_classes_not_to_list_invert"]').length>0) {
			jQuery('input[name="clever_point_shipping_classes_not_to_list_invert"]').parents('tr').hide();
		}
		if (jQuery('label[for="clever_point_categories_not_to_list"]').length>0) {
			var $this=jQuery('label[for="clever_point_categories_not_to_list"]');
			var input = jQuery($this).parents("tr").next('tr').find('input');
			var default_state= clever_point_ajax_object.lang_exclude;
			if (input.is(':checked') ) {
				var default_state=clever_point_ajax_object.lang_include;
			}
			var text=jQuery('label[for="clever_point_categories_not_to_list"]').text()
				.replace(/#/,'<a class="cleverpoint-smart-switch" data-lang-exclude="'+clever_point_ajax_object.lang_exclude+'" data-lang-include="'+clever_point_ajax_object.lang_include+'">'+default_state)
				.replace(/#/,'</a>');
			console.log(text);
			jQuery('label[for="clever_point_categories_not_to_list"]').html(text);
		}
		if (jQuery('label[for="clever_point_tags_not_to_list"]').length>0) {
			var $this=jQuery('label[for="clever_point_tags_not_to_list"]');
			var input = jQuery($this).parents("tr").next('tr').find('input');
			var default_state= clever_point_ajax_object.lang_exclude;
			if (input.is(':checked') ) {
				var default_state=clever_point_ajax_object.lang_include;
			}
			var text=jQuery('label[for="clever_point_tags_not_to_list"]').text()
				.replace(/#/,'<a class="cleverpoint-smart-switch" data-lang-exclude="'+clever_point_ajax_object.lang_exclude+'" data-lang-include="'+clever_point_ajax_object.lang_include+'">'+default_state)
				.replace(/#/,'</a>');
			jQuery('label[for="clever_point_tags_not_to_list"]').html(text);
		}
		if (jQuery('label[for="clever_point_shipping_classes_not_to_list"]').length>0) {
			var $this=jQuery('label[for="clever_point_shipping_classes_not_to_list"]');
			var input = jQuery($this).parents("tr").next('tr').find('input');
			var default_state= clever_point_ajax_object.lang_exclude;
			if (input.is(':checked') ) {
				var default_state=clever_point_ajax_object.lang_include;
			}
			var text=jQuery('label[for="clever_point_shipping_classes_not_to_list"]').text()
				.replace(/#/,'<a class="cleverpoint-smart-switch" data-lang-exclude="'+clever_point_ajax_object.lang_exclude+'" data-lang-include="'+clever_point_ajax_object.lang_include+'">'+default_state)
				.replace(/#/,'</a>');
			jQuery('label[for="clever_point_shipping_classes_not_to_list"]').html(text);
		}
		jQuery('.cleverpoint-smart-switch').on('click',function (e){
			var $this=jQuery(this);
			var input = jQuery($this).parents("tr").next('tr').find('input');
			if (input.is(':checked') ) {
				$this.text($this.data('lang-exclude'));
				input.prop('checked', false)
			}else {
				$this.text($this.data('lang-include'));
				input.prop('checked', true)
			}
			e.preventDefault();
		});
		jQuery('.selectWoo').selectWoo();
		jQuery('#clever_point_create_voucher').on("click",function (e) {
			var $this=jQuery(this);
			e.preventDefault();
			$this.addClass('disabled').addClass('is-active');
			jQuery.ajax({
				type : "post",
				dataType : "json",
				url : clever_point_ajax_object.ajax_url,
				data : { action: "clever_point_create_voucher", order_id : $this.data('order'),comments: jQuery('textarea[name="clever_point_comments"]').val(),cod: jQuery('input[name="clever_point_cod"]').val(),weight: jQuery('input[name="clever_point_weight"]').val(),courier: jQuery('select[name="clever_point_courier"]').val(),courier_voucher: jQuery('input[name="clever_point_courier_voucher"]').val()},
				success: function(response) {
					$this.removeClass('disabled').removeClass('is-active');
					if(response === "success") {
						alert($this.data('success'));
						location.reload();
					}
					else {
						alert(response);
						console.log(response);
					}
				}
			})
		});

		jQuery('.clever_point_cancel_voucher').on("click",function (e) {
			e.preventDefault();
			var $this=jQuery(this);
			$this.addClass('disabled').addClass('is-active');
			jQuery.ajax({
				type : "post",
				dataType : "json",
				url : clever_point_ajax_object.ajax_url,
				data : {action: "clever_point_cancel_voucher", order_id : $this.data('order')},
				success: function(response) {
					$this.removeClass('disabled').removeClass('is-active');
					if(response.success) {
						alert($this.data('success'));
						location.reload();
					}
					else {
						console.log(response);
					}
				}
			})
		});

		jQuery('#clever_point_print_voucher').on("click",function (e) {
			e.preventDefault();
			var $this=jQuery(this);
			$this.addClass('disabled').addClass('is-active');
			jQuery.ajax({
				type : "post",
				dataType : "json",
				url : clever_point_ajax_object.ajax_url,
				data : {action: "clever_point_print_voucher", print_type: jQuery('#clever_point_print_voucher_type').val(), order_id :$this.data('order')},
				success: function(response) {
					console.log(response);
					$this.removeClass('disabled').removeClass('is-active');
					if (response.url)
						window.open(response.url, "_blank");
					else
						console.log(response);
				}
			})
		});
	});

})( jQuery );
