var instagram_list_images = new Array();

function loadImage(path, width, height, target) {
	
    jQuery('<img src="'+ path +'">').load(function() {
      jQuery(this).width(width).height(height).appendTo(target);
    });
}

// inyecta al formulario la informacion de la imagen, y le cambia la clase
function instagram_selectImage (num, elem) {
	if (jQuery(elem).attr('class') == "selected") {
		jQuery(elem).attr('class', '');
		jQuery('#inst_input_' + num).remove();
	
	} else { 
		jQuery(elem).attr('class', 'selected');
		
		jQuery('#instagram_upload_form').append(
				'<div id="inst_input_' + num + '"><input type="hidden" name="instagramimage[]" value="' + instagram_list_images[num]['src'] + '" />' +
				'<input type="hidden" name="instagramimagename[]" value="' + instagram_list_images[num]['name'] + '" />' +
				'<input type="hidden" name="instagramimagecaption[]" value="' + instagram_list_images[num]['caption'] + '" /></div>');
	}
	
}