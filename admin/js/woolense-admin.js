(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */


	$(document).ready(function(){

		//On selecting new image
		$('#woolense-upload').on('click', function(e){
			e.preventDefault();
			var img = wp.media({
				title:"Select image for woolense",
				multiple:false,
			}).open().on('select', function(data){
				//Reset color output upon selecting a new image
				$('.woolense-color-output').html('');

				//Reset the value of hidden field upon selecting a new image
				$('#woolense_color_data').val('');
				
				var files = img.state().get("selection");
				var jsonFiles = files.toJSON();
				var imageHtml = '<div class="woolense-product-preview"><img id="image" src="'+jsonFiles[0].url+'" /></div>';
				$('.woolense-product-preview-box').html(imageHtml);

				
				
				$('.woolense-loader').show();
				var data = {
					'action': 'woolense_color_ajax_action',
					'imgurl': jsonFiles[0].url,
					'x':20,
					'y':20,
					'w':20,
					'h':20
				};

				$.post(ajaxurl, data, function(response) {
					$('.woolense-loader').hide();
					if(response == 'Error') {
						$('.woolense-color-output').html('');
						$('.woolense-color-output').show();
					}
					else {
						//Inserting the response in the hidden field
						$('#woolense_color_data').val('['+response+']');

						$('.woolense-color-output').html('');
						try {
							response = JSON.parse(response);
							for(var i=0;i<response.colors.length;i++){
								$('.woolense-color-output').append('<div class="single-color" style="background:' + response.colors[i] + ';"></div>');
							}
							$('.woolense-color-output').show();
						}
						catch {
							console.log('Error extracting the color of the image.Please select another.');
						}
					}
				});
				
			});
		});

		//Preventing default for all buttons
		$('.woolense-dropbtn').on('click', function(e){
			e.preventDefault();
		});



		//Dropdown show-hide functionality
		$(".woolense-dropdown").on({
			mouseenter: function () {
				$(this).find('.woolense-dropdown-content').toggle();
			},
			mouseleave: function () {
				$(this).find('.woolense-dropdown-content').toggle();
			}
		});


		//Cancel functionality
		$('.woolense-cancel').click(function() {
			$('.woolense-color-output').html('');
			$('.woolense-color-output').hide();
			$('#woolense_color_data').val('');
			$('.woolense-product-preview-box').html('');
		});






	});

})( jQuery );
