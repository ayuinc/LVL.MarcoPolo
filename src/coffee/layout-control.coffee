'use-strict'
$(document).ready ->
	# DISABLE ANCHORS
	$('.disable-anchors a').click (e)->
		e.preventDefault()
		return

	# HREFerence for anchor blocks 
	$('[data-href]').click((e)->
		locationArr = window.location.pathname.split('/')
		lastPath = locationArr[locationArr.length - 1]
		mainPath = locationArr[locationArr.length - 2]
		pathObj = {}
		pathObj[mainPath] = lastPath
		window.history.pushState(pathObj, '', lastPath)
		document.location.replace($(this).data('href'))
		return
		)
	
	# TRANSFORM ICONS INITIALIZE
	# transformicons.add('.tcon')
	bodyScrollController = new ScrollMagic.Controller()

	# SCROLL TO
	bodyScrollController.scrollTo((newpos)->
		TweenMax.to(window, 0.5, {scrollTo: {y: newpos}})
		return
		)

	lockBody = ()->
		body = 'body'
		$(body).css({
			overflow: 'hidden'
			})
		bodyScrollController.scrollTo(body)
		return

	$('.filter-triggers a').click((e)->
		e.preventDefault()
		lockBody() 
		$('.filters-wrapper [class*=filter-module-]').removeClass('on-screen')
		idRef = $(this).attr('href')
		console.log idRef
		$('.filter-triggers li').removeClass 'active'
		$('.site-wrapper').addClass 'filters-on'
		$(this).parent().addClass 'active'
		$('.filters-wrapper').find(idRef).addClass('on-screen')
		return
		)

	$('.filters-close a').click((e)->
		e.preventDefault()
		$('.filter-triggers li').removeClass 'active'
		$('.site-wrapper').removeClass 'filters-on'
		$('body').css({
			overflow: 'auto'
			})
		return
		)

	# POSTS GALLERY
	$gallery = $('.gallery')
	$gallery.flickity({
		cellAlign: 'left'
		contain: true
		wrapAround: true
		pageDots: false
		prevNextButtons: false
		})

	$galleryControlLeft = $('.gallery-control-left')
	$galleryControlRight = $('.gallery-control-right')

	$galleryControlLeft.on('click', 'a', (e)->
		e.preventDefault()
		$gallery.flickity('previous')
		return
		)

	$galleryControlRight.on('click', 'a', (e)->
		e.preventDefault()
		$gallery.flickity('next')
		return
		)

	return # END ON READY

























