jQuery(document).ready(function($){
//Live Preview
//Color Scheme
jQuery('#color_scheme input:radio[name="nwn_options[color_scheme]"]').change(function($){
   var css = jQuery(this).attr("value");
   jQuery(".header").css('background-color',jQuery(this).val());
   jQuery(".spreview").css('color',jQuery(this).val());
});

//Message
jQuery('#message-p input').keyup(function($){
	var keyed = jQuery(this).val();
      jQuery('.message-text').html(keyed);
});

//Location (Shortcode)
jQuery('input:checkbox[name="nwn_options[shortcode]"]').change(function($) {
    if(this.checked) {
        jQuery('.spreview').fadeIn();
    }
    else{
    	jQuery('.spreview').fadeOut();
    }
});
//Location (Headerbar)
jQuery('input:checkbox[name="nwn_options[headerbar]"]').change(function($) {
    if(this.checked) {
        jQuery('.header').fadeIn();
    }
    else{
    	jQuery('.header').fadeOut();
    }
});

//Icons
$('select[name="nwn_options[icon]"]').change(function(){
  var value = $('select[name="nwn_options[icon]"] option:selected').val();
  $('p.message-preview i').removeClass().addClass('fa ' + value);
});

//Date Picker JS

  $('#datetimepicker').datetimepicker({
  format:'m/d/Y H:i'
  });

// Color Scheme Toggle JS

  jQuery('label').click(function($){
    $(this).children('span').addClass('input-checked');
    $(this).parent('.toggle').siblings('.toggle').children('label').children('span').removeClass('input-checked');
});

//Datatables
jQuery(document).ready( function ($) {
  var table = $('#sites').DataTable();
});

});