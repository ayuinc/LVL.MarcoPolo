$(document).ready ->

	# ISOTOPE GRIDS
	$container = $('.pins-grid')
	# $container.children('li').addClass('isotope-item')
	$container.imagesLoaded ->
		$container.isotope({
			itemSelector: '.grid-list-item'
			layoutMode: 'fitRows'
			})
		return

	# ISOTOPE APPEND ELEMENTS
	itemTempl = '<div class="grid-list-item">' +
				    '<div class="pin">' +
				      '<div class="pin-top">' +
				        '<small class="pin-category">{categories show_group="1"}{category_name}{/categories} in {categories show_group="3"}{category_name}{/categories}</small>' +
				        '<h2 class="pin-title"><a href="{site_url}project/{url_title}">{title}</a></h2>' +
				        '<em class="pin-location">{categories show_group="2"}{category_name}{/categories}</em>' +
				      '</div>' +
				      '<div class="pin-center">' +
				        '<div class="pin-pic">' +
				          '{project_imagenes limit="1"}' +
				          '<img src="{image:url:small}" alt="Grid 1" class="ignore-srcset">' +
				          '{/project_imagenes}' +
				          '<a href="{site_url}project/{url_title}" class="overlay">' +
				            '<div class="pin-bottom">' +
				              '<button class="btn btn-icon-custom btn-gray-darker btn-icon-left btn-sm">' +
				                '<i>45</i>' +
				                '<span>VOTE</span>' +
				              '</button>' +
				            '</div>' +
				          '</a>' +
				        '</div>' +
				      '</div>' +
				    '</div>' +
				  '</div>'

	return # END ON READY