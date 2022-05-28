/**
 * JavaScript background to FakeSample.php
 *
 * This uses jQuery, so you have to have it enabled.-
 *
 * Author   : Yoel Monsalve.
 * Date     : 2022-05-27
 * Version  : ---
 * Modified : 2022-05-27 
 */

function stylizeTable() {

  table = $('#table-drug');
  if (table) {
    action = table.attr('data-action');

    if (action == 'caution') {
      /* stylize elements according to 'CAUTION' */
      $('#col-drug').addClass('drug-caution-light');
      $('#col-phenotype').addClass('drug-caution-light');
      $('#drug-action').addClass('drug-caution-bold');
      $('#drug-group-phenotype').addClass('drug-caution-bold');
    }
    else if (action == 'go') {
      $('#col-drug').addClass('drug-ok-light');
      $('#col-phenotype').addClass('drug-ok-light');
      $('#drug-action').addClass('drug-ok-bold');
      $('#drug-group-phenotype').addClass('drug-ok-bold');
    }
    else {
      $('#col-drug').addClass('drug-no-action');
      $('#col-phenotype').addClass('drug-no-action');
      $('#drug-action').addClass('drug-no-action');
      $('#drug-group-phenotype').addClass('drug-no-action'); 
    }
  }

  /* set click event */
  table.click(function(e){
    toggleTable();
    e.preventDefault();
  })
}

/**
 * Toogle hiden/visible table
 */
function toggleTable() {
  $('.hiddenable').toggleClass('col-hidden');

  table = $('#table-drug');
  table.toggleClass('table-drug-collapsed');

  if (table.hasClass('table-drug-collapsed')) {
    //table.attr('data-toggle', "tooltip");
    //table.attr('data-placement', "right");
    //table.attr('title', "Click to see detail");
    //table.tooltip('enable')
  }
  else {
    //table.removeAttr('data-toggle')
    //table.removeAttr('data-placement')
    //table.removeAttr('title')
    //table.tooltip('disable')
  }
}

/* to execute on document load */
$(document).ready(function() {

  stylizeTable();
  toggleTable();    // show table initially collapsed

  /* enable tooltips */
  $('[data-toggle="tooltip"]').tooltip();
})