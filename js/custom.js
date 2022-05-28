/* JS custom file
 * To additional effects
 *
 * Author: Yoel Monsalve.
 * Date:   April, 2021.
 */

$(document).ready(function() {

	const vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0)
	const vh = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0)

	// Close any open menu accordions when window is resized below 768px
	if ( vw < 768 || $(window).width() < 768 ) {
  	$('.sidebar .collapse').collapse('hide');
  	$('.navbar-nav.sidebar').addClass('toggled');
	}
})