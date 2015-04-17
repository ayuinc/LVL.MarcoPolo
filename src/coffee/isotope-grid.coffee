# $(document).ready ->

	# # ISOTOPE GRIDS
	# $container = $('.pins-grid')
	# # $container.children('li').addClass('isotope-item')
	# $container.imagesLoaded ->
	# 	$container.isotope({
	# 		itemSelector: '.grid-list-item'
	# 		layoutMode: 'fitRows'
	# 		})
	# 	return

	# # ISOTOPE APPEND ELEMENTS
	# itemTempl = '<div class="grid-list-item">' +
	# 				  '<div class="pin">' +
	# 				    '<div class="pin-top">' +
	# 				      '<small class="pin-category">{categories show_group="1"}{category_name}{/categories} in {categories show_group="3"}{category_name}{/categories}</small>' +
	# 				      '<h2 class="pin-title"><a href="{site_url}project/{url_title}">{title}</a></h2>' +
	# 				      '<em class="pin-location">{categories show_group="2"}{category_name}{/categories}</em>' +
	# 				    '</div>' +
	# 				    '<div class="pin-center">' +
	# 				      '<div class="pin-pic">' +
	# 				        '{project_imagenes limit="1"}' +
	# 				        '<img src="{image:url:small}" alt="Grid 1" class="ignore-srcset">' +
	# 				        '{/project_imagenes}' +
	# 				        '<a href="{site_url}project/{url_title}" class="overlay">' +
	# 				          '<div class="pin-bottom">' +
	# 				            '{exp:rating:form' +
	# 				              'entry_id="{entry_id}"' +
	# 				              'collection="Votos"' +
	# 				              'return="{site_url}"' +
	# 				              'anonymous="yes"' +
	# 				            '}' +
	# 				            '<input type="hidden" name="rating" id="rating" value="1">' +
	# 				            '<button type="submit" name="submit" value="Vota" class="btn btn-icon-custom btn-gray-darker btn-icon-left">' +
	# 				              '<i>' +
	# 				                '{exp:rating:stats entry_id="{entry_id}"}' +
	# 				                  '{overall_count}' +
	# 				                  '{if rating_no_results}' +
	# 				                    '0' +
	# 				                  '{/if}' +
	# 				                '{/exp:rating:stats}' +
	# 				              '</i>' +
	# 				              '<span>' +
	# 				                'VOTE' +
	# 				              '</span>' +
	# 				            '</button> ' +
	# 				            '{/exp:rating:form}' +
	# 				          '</div>' +
	# 				        '</a>' +
	# 				      '</div>' +
	# 				    '</div>' +
	# 				  '</div>' +
	# 				'</div>'

	# return # END ON READY