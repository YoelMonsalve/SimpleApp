/*  Javascript methods to complete the functionality 
 *  and user experience for the page
 *
 * Author   : Yoel Monsalve.
 * Date     : 2022-05-29
 * Version  : ---
 * Modified : 2022-05-29
 */

$(document).ready( function(e) {

  /* if JS is available, merge both buttons (upload + submit),
   * in a single one */
  $('#search_file').css('display', 'none');    // hide the select button
  $('#file_upload').data('mode', 'new');
  $('#file_upload').data('id', 0);

  $('#submit').click( function(e, file_chosen=false){
    if (!$(this).data('file_chosen')) {
      $('#file_upload').click()
      e.preventDefault()
    }
    else {
      //$(this).data('file_chosen', false)
      // continue normal execution, via FORM/POST
      ;
    }
  })

  $('#file_upload').click( function() {
    if (!window.File || !window.FileReader || !window.FileList || !window.Blob) {
        alert('The File APIs are not fully supported in this browser.');
        return;
      } 
  })

  $('#file_upload').change( function() {
    if ($(this).data('mode') == 'new') {
      /* upload a new image */
      $('#submit').data('file_chosen', true);
      $('#submit').trigger('click', true)
    }
    else if ($(this).data('mode') == 'change') {
      if ($(this).data('id')) {
        media_id = parseInt($(this).data('id'));
        if (media_id && media_id >= 1) {
          //change_image(media_id)
        }
      }
    }
  })

  $('#search_file').click( function(e) {
    $('#file_upload').data('mode', 'new');
    $('#file_upload').click()
  });

  // on loading, not any file is chosen
  $('#submit').data('file_chosen', false);
  
  /* enable tooltips */
  //$('[data-toggle="tooltip"]').tooltip()
});
