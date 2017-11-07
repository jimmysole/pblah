function editPhoto(select_element) {
	if (select_element.value == 1) {
		// hide any other selected options
		// crop
		$('#blur').attr('style', 'display: none;');
		$('#enhance').attr('style', 'display: none');
		$('#thumbnail').attr('style', 'display: none;');
		$('#thumbnail-message').attr('style', 'display: none;');
		$('#enhance-message').attr('style', 'display: none;');
		$('#blur-message').attr('style', 'display: none;');
		$('#sepia').attr('style', 'display: none;');
		$('#sepia-message').attr('style', 'display: none;');
		$('#black-white').attr('style', 'display: none;');
		$('#bw-message').attr('style', 'display: none;');
		
		$('#crop').attr('style', 'display: initial;');
		$('#crop-2').attr('style', 'display: initial;');
		$('#button-holder').attr('style', 'display: initial;');
		
		$('#save-photo').on('click', function(e) {
			e.preventDefault();
			
			$.ajax({
				url: "/members/profile/handle-photo-edit",
				type: "POST",
				dataType: "json",
				data: {
					crop_image: 1,
					album_name: $('#album-name option:selected').val(),
					photo: /[^/]*$/.exec($('#selected-photo img').attr('src'))[0],
					width:  $('#crop-width').val(),
					height: $('#crop-height').val(),
					x:      $("#crop-x").val(),
					y:      $("#crop-y").val()
				}
			}).done(function(msg) {
				$('#message').attr('style', 'display: initial;');
				$('#crop-msg').html(msg.success);
			}).fail(function(msg) {
				$('#message').attr('style', 'display: initial;');
				$('#crop-msg').html(msg.fail);
			});
		});
	} else if (select_element.value == 2) {
		// blur
		$('#enhance').attr('style', 'display: none;');
		$('#thumbnail').attr('style', 'display: none;')
		$('#thumbnail-message').attr('style', 'display: none;');
		$('#crop').attr('style', 'display: none;');
		$('#crop-2').attr('style', 'display: none');
		$('#button-holder').attr('style', 'display: none;');
		$('#enhance-message').attr('style', 'display: none;');
		$('#crop-message').attr('style', 'display: none;');
		$('#sepia').attr('style', 'display: none;');
		$('#sepia-message').attr('style', 'display: none;');
		$('#black-white').attr('style', 'display: none;');
		$('#bw-message').attr('style', 'display: none;');
		
		$('#blur').attr('style', 'display: initial;');

		$('#button-holder-blur').attr('style', 'display: initial;');

		$('#save-photo-blur').on('click', function(e) {
			e.preventDefault();

			$.ajax({
				url: "/members/profile/handle-photo-edit",
				type: "POST",
				dataType: "json",
				data: {
					blur_image: 1,
					album_name: $('#album-name option:selected').val(),
					photo: /[^/]*$/.exec($('#selected-photo img').attr('src'))[0],
					radius: $('#radius').val(),
					sigma: $('#sigma').val()
				}
			}).done(function(msg) {
				$('#blur-message').attr('style', 'display: initial;');
				$('#blur-msg').html(msg.success_blur);
			}).fail(function(msg) {
				$('#blur-message').attr('style', 'display: initial;');
				$('#blur-msg').html(msg.fail_blur);
			});
		});
	} else if (select_element.value == 3) {
		// enhance
		$('#blur').attr('style', 'display: none;');
		$('#thumbnail').attr('style', 'display: none;');
		$('#thumbnail-message').attr('style', 'display: none;');
		$('#crop').attr('style', 'display: none;');
		$('#crop-2').attr('style', 'display: none');
		$('#button-holder').attr('style', 'display: none;');
		$('#blur-message').attr('style', 'display: none;');
		$('#crop-message').attr('style', 'display: none;');
		$('#sepia').attr('style', 'display: none;');
		$('#sepia-message').attr('style', 'display: none;');
		$('#black-white').attr('style', 'display: none;');
		$('#bw-message').attr('style', 'display: none;');
		
		$('#enhance').attr('style', 'display: initial;');

		$('#save-enhanced-photo').on('click', function(e) {
			e.preventDefault();

			$.ajax({
				url: "/members/profile/handle-photo-edit",
				type: "POST",
				dataType: "json",
				data: {
					enhance_image: 1,
					album_name: $('#album-name option:selected').val(),
					photo: /[^/]*$/.exec($('#selected-photo img').attr('src'))[0]
				}
			}).done(function(msg) {
				$('#enhance-message').attr('style', 'display: initial;');
				$('#enhance-msg').html(msg.success_enhance);
			}).fail(function(msg) {
				$('#enhance-message').attr('style', 'display: initial;');
				$('#enhance-msg').html(msg.fail_enhance);
			});
		});
	} else if (select_element.value == 4) {
		// thumbnail
		$('#enhance').attr('style', 'display: none;');
		$('#blur').attr('style', 'display: none;')
		$('#crop').attr('style', 'display: none;');
		$('#crop-2').attr('style', 'display: none');
		$('#button-holder').attr('style', 'display: none;');
		$('#save-enhance-photo').attr('style', 'display: none;');
		$('#enhance-message').attr('style', 'display: none;');
		$('#blur-message').attr('style', 'display: none;');
		$('#crop-message').attr('style', 'display: none;');
		$('#sepia').attr('style', 'display: none;');
		$('#sepia-message').attr('style', 'display: none;');
		$('#black-white').attr('style', 'display: none;');
		$('#bw-message').attr('style', 'display: none;');

		
		$('#thumbnail').attr('style', 'display: initial;');

		$('#save-thumbnail-photo').on('click', function(e) {
			e.preventDefault();

			$.ajax({
				url: "/members/profile/handle-photo-edit",
				type: "POST",
				dataType: "json",
				data: {
					thumbnail_image: 1,
					album_name: $('#album-name option:selected').val(),
					photo: /[^/]*$/.exec($('#selected-photo img').attr('src'))[0],
					tx: $('#crop-tx').val(),
					ty: $('#crop-ty').val()
				}
			}).done(function(msg) {
				$('#thumbnail-message').attr('style', 'display: initial;');
				$('#thumbnail-msg').html(msg.success_thumbnail);
			}).fail(function(msg) {
				$('#thumbnail-message').attr('style', 'display: initial;');
				$('#thumbnail-msg').html(msg.fail_thumbnail);
			});
		});
	} else if (select_element.value == 5) {
		// sepia tone
		$('#enhance').attr('style', 'display: none;');
		$('#blur').attr('style', 'display: none;')
		$('#crop').attr('style', 'display: none;');
		$('#thumbnail').attr('style', 'display: none;');
		$('#crop-2').attr('style', 'display: none');
		$('#button-holder').attr('style', 'display: none;');
		$('#button-blur-holder').attr('style', 'display: none;');
		$('#blur-message').attr('style', 'display: none;');
		$('#enhance-message').attr('style', 'display: none;');
		$('#crop-message').attr('style', 'display: none;');
		$('#black-white').attr('style', 'display: none;');
		$('#bw-message').attr('style', 'display: none;');

		$('#sepia').attr('style', 'display: initial;');

		$('#save-sepia-photo').on('click', function(e) {
			e.preventDefault();

			$.ajax({
				url: "/members/profile/handle-photo-edit",
				type: "POST",
				dataType: "json",
				data: {
					sepia_image: 1,
					album_name: $('#album-name option:selected').val(),
					photo: /[^/]*$/.exec($('#selected-photo img').attr('src'))[0],
					sepia_threshold: $('#sepia-threshold').val()
				}
			}).done(function(msg) {
				$('#sepia-message').attr('style', 'display: initial;');
				$('#sepia-msg').html(msg.success_sepia);
			}).fail(function(msg) {
				$('#sepia-message').attr('style', 'display: initial;');
				$('#sepia-msg').html(msg.fail_sepia);
			});
		});
	} else if (select_element.value == 6) {
		// black and white
		$('#enhance').attr('style', 'display: none;');
		$('#blur').attr('style', 'display: none;')
		$('#crop').attr('style', 'display: none;');
		$('#thumbnail').attr('style', 'display: none;');
		$('#crop-2').attr('style', 'display: none');
		$('#button-holder').attr('style', 'display: none;');
		$('#button-blur-holder').attr('style', 'display: none;');
		$('#blur-message').attr('style', 'display: none;');
		$('#enhance-message').attr('style', 'display: none;');
		$('#crop-message').attr('style', 'display: none;');
		$('#sepia').attr('style', 'display: none;');
		$('#sepia-message').attr('style', 'display: none;');
		
		$('#black-white').attr('style', 'display: initial;');
		
		$('#save-bw-photo').on('click', function(e) {
			e.preventDefault();
			
			$.ajax({
				url: "/members/profile/handle-photo-edit",
				type: "POST",
				dataType: "json",
				data: {
					bw_image: 1,
					album_name: $('#album-name option:selected').val(),
					photo: /[^/]*$/.exec($('#selected-photo img').attr('src'))[0],
					colorspace: $('#colorspace option:selected').val(),
					channel: $('#channel option:selected').val()
				}
			}).done(function(msg) {
				$('#bw-message').attr('style', 'display: initial;');
				$('#bw-msg').html(msg.success_bw);
			}).fail(function(msg) {
				$('#bw-message').attr('style', 'display: initial;');
				$('#bw-msg').html(msg.fail_bw);
			});
		});
	} else { 
		// blank option selected, hide everything
		$('#enhance').attr('style', 'display: none;');
		$('#blur').attr('style', 'display: none;')
		$('#crop').attr('style', 'display: none;');
		$('#thumbnail').attr('style', 'display: none;');
		$('#crop-2').attr('style', 'display: none');
		$('#button-holder').attr('style', 'display: none;');
		$('#button-blur-holder').attr('style', 'display: none;');
		$('#blur-message').attr('style', 'display: none;');
		$('#enhance-message').attr('style', 'display: none;');
		$('#crop-message').attr('style', 'display: none;');
		$('#sepia').attr('style', 'display: none;');
		$('#sepia-message').attr('style', 'display: none;');
		$('#black-white').attr('style', 'display: none;');
		$('#bw-message').attr('style', 'display: none;');
	}
}