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

	return # END ON READY