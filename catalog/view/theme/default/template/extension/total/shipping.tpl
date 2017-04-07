<div class="panel panel-default">
  <div class="panel-heading">
    <h4 class="panel-title"><a href="#collapse-shipping" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion"><?php echo $heading_title; ?> <i class="fa fa-caret-down"></i></a></h4>
  </div>
  <div id="collapse-shipping" class="panel-collapse collapse">
    <div class="panel-body">
      <p><?php echo $text_shipping; ?></p>
      <div class="form-horizontal">
        <div class="form-group required">
          <label class="col-sm-2 control-label" for="input-country"><?php echo $entry_country; ?></label>
          <div class="col-sm-10">
            <select name="country_id" id="input-country" class="form-control">
              <option value=""><?php echo $text_select; ?></option>
              <?php foreach ($countries as $country) { ?>
              <?php if ($country['country_id'] == $country_id) { ?>
              <option value="<?php echo $country['country_id']; ?>" selected="selected"><?php echo $country['name']; ?></option>
              <?php } else { ?>
              <option value="<?php echo $country['country_id']; ?>"><?php echo $country['name']; ?></option>
              <?php } ?>
              <?php } ?>
            </select>
          </div>
        </div>
        <div class="form-group required">
          <label class="col-sm-2 control-label" for="input-zone"><?php echo $entry_zone; ?></label>
          <div class="col-sm-10">
            <select name="zone_id" id="input-zone" class="form-control">

        <!---frd 1-->
        <?php if (!empty($zone_id)) {?>
        <option value="<?php echo $zone_id; ?>"></option>
        <?php } ?>
        <!---frd-->

            </select>
          </div>
        </div>

        <!--frd 2-->
        <div class="form-group required">
          <label class="col-sm-2 control-label" for="input-district"><?php echo $entry_district; ?></label>
          <div class="col-sm-10">
            <select name="district_id" id="input-district" class="form-control">
              <?php if (!empty($district_id)) {?>
              <option value="<?php echo $district_id; ?>"></option>
              <?php } ?>
            </select>
            <!--<?php if ($error_district) { ?>
            <div class="text-danger"><?php echo $error_district; ?></div>
            <?php } ?>-->
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label" for="input-subdistrict"><?php echo $entry_subdistrict; ?></label>
          <div class="col-sm-10">
            <select name="subdistrict_id" id="input-subdistrict" class="form-control">
              <?php if (!empty($subdistrict_id)) {?>
              <option value="<?php echo $subdistrict_id; ?>"></option>
              <?php } ?>
            </select>
            <!--<?php if ($error_district) { ?>
            <div class="text-danger"><?php echo $error_district; ?></div>
            <?php } ?>-->
          </div>
        </div>

        <!---->

        <div class="form-group required">
          <label class="col-sm-2 control-label" for="input-postcode"><?php echo $entry_postcode; ?></label>
          <div class="col-sm-10">
            <input type="text" name="postcode" value="<?php echo $postcode; ?>" placeholder="<?php echo $entry_postcode; ?>" id="input-postcode" class="form-control" />
          </div>
        </div>
        <button type="button" id="button-quote" data-loading-text="<?php echo $text_loading; ?>" class="btn btn-primary"><?php echo $button_quote; ?></button>
      </div>
      <script type="text/javascript"><!--
$('#button-quote').on('click', function() {
	$.ajax({
		url: 'index.php?route=extension/total/shipping/quote',
		type: 'post',

        /*frd 3*/
        data: 'country_id=' + $('select[name=\'country_id\']').val() + '&zone_id=' + $('select[name=\'zone_id\']').val() + '&postcode=' + encodeURIComponent($('input[name=\'postcode\']').val()) + '&district_id=' + $('select[name=\'district_id\']').val() + '&subdistrict_id=' + $('select[name=\'subdistrict_id\']').val(),

		dataType: 'json',
		beforeSend: function() {
			$('#button-quote').button('loading');
		},
		complete: function() {
			$('#button-quote').button('reset');
		},
		success: function(json) {
			$('.alert, .text-danger').remove();

			if (json['error']) {
				if (json['error']['warning']) {
					$('.breadcrumb').after('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + json['error']['warning'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');

					$('html, body').animate({ scrollTop: 0 }, 'slow');
				}

				if (json['error']['country']) {
					$('select[name=\'country_id\']').after('<div class="text-danger">' + json['error']['country'] + '</div>');
				}

				if (json['error']['zone']) {
					$('select[name=\'zone_id\']').after('<div class="text-danger">' + json['error']['zone'] + '</div>');
				}


        /*frd 6*/
        if (json['error']['district']) {
					$('select[name=\'district_id\']').after('<div class="text-danger">' + json['error']['district'] + '</div>');
				}
        /*--*/

				if (json['error']['postcode']) {
					$('input[name=\'postcode\']').after('<div class="text-danger">' + json['error']['postcode'] + '</div>');
				}
			}

			if (json['shipping_method']) {
				$('#modal-shipping').remove();

				html  = '<div id="modal-shipping" class="modal">';
				html += '  <div class="modal-dialog">';
				html += '    <div class="modal-content">';
				html += '      <div class="modal-header">';
				html += '        <h4 class="modal-title"><?php echo $text_shipping_method; ?></h4>';
				html += '      </div>';
				html += '      <div class="modal-body">';

				for (i in json['shipping_method']) {
					html += '<p><strong>' + json['shipping_method'][i]['title'] + '</strong></p>';


					if (!json['shipping_method'][i]['error']) {
						for (j in json['shipping_method'][i]['quote']) {
							html += '<div class="radio">';
							html += '  <label>';

							/*replace if (json['shipping_method'][i]['quote'][j]['code'] == '<?php echo $shipping_method; ?>') {
								html += '<input type="radio" name="shipping_method" value="' + json['shipping_method'][i]['quote'][j]['code'] + '" checked="checked" />';
							} else {
								html += '<input type="radio" name="shipping_method" value="' + json['shipping_method'][i]['quote'][j]['code'] + '" />';
							}*/
              if (json['shipping_method'][i]['quote'][j]['code'] == '<?php echo $shipping_method; ?>') {
								html += '<input type="radio" name="shipping_method" value="' + json['shipping_method'][i]['quote'][j]['code'] + '" checked="checked" />' + '<img src="' +  json['shipping_method'][i]['quote'][j]['icon'] + '" alt="" style="width:64px;height:32px;">';
							} else {
								html += '<input type="radio" name="shipping_method" value="' + json['shipping_method'][i]['quote'][j]['code'] + '" />' + '<img src="' +  json['shipping_method'][i]['quote'][j]['icon'] + '" alt="" style="width:64px;height:32px;">';
							}


        /*frd 4*/
        if(typeof(json['shipping_method'][i]['quote'][j]['etd']) != "undefined" && json['shipping_method'][i]['quote'][j]['etd'] !== null) {
          html += json['shipping_method'][i]['quote'][j]['title'] + ' - ' + json['shipping_method'][i]['quote'][j]['text'] + '<p style="font-size:85%;">Est: ' + json['shipping_method'][i]['quote'][j]['etd'] + '</p>' + '</label></div>';
        } else {
          html += json['shipping_method'][i]['quote'][j]['title'] + ' - ' + json['shipping_method'][i]['quote'][j]['text'] + '</label></div>';
        }
        /*--*/

						}
					} else {
						html += '<div class="alert alert-danger">' + json['shipping_method'][i]['error'] + '</div>';
					}
				}

				html += '      </div>';
				html += '      <div class="modal-footer">';
				html += '        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $button_cancel; ?></button>';

				<?php if ($shipping_method) { ?>
				html += '        <input type="button" value="<?php echo $button_shipping; ?>" id="button-shipping" data-loading-text="<?php echo $text_loading; ?>" class="btn btn-primary" />';
				<?php } else { ?>
				html += '        <input type="button" value="<?php echo $button_shipping; ?>" id="button-shipping" data-loading-text="<?php echo $text_loading; ?>" class="btn btn-primary" disabled="disabled" />';
				<?php } ?>

				html += '      </div>';
				html += '    </div>';
				html += '  </div>';
				html += '</div> ';

				$('body').append(html);

				$('#modal-shipping').modal('show');

				$('input[name=\'shipping_method\']').on('change', function() {
					$('#button-shipping').prop('disabled', false);
				});
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});

$(document).delegate('#button-shipping', 'click', function() {
	$.ajax({
		url: 'index.php?route=extension/total/shipping/shipping',
		type: 'post',
		data: 'shipping_method=' + encodeURIComponent($('input[name=\'shipping_method\']:checked').val()),
		dataType: 'json',
		beforeSend: function() {
			$('#button-shipping').button('loading');
		},
		complete: function() {
			$('#button-shipping').button('reset');
		},
		success: function(json) {
			$('.alert').remove();

			if (json['error']) {
				$('.breadcrumb').after('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + json['error'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');

				$('html, body').animate({ scrollTop: 0 }, 'slow');
			}

			if (json['redirect']) {
				location = json['redirect'];
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});
//--></script>
<script type="text/javascript"><!--
$('select[name=\'country_id\']').on('change', function() {
	$.ajax({
		url: 'index.php?route=extension/total/shipping/country&country_id=' + this.value,
		dataType: 'json',
		beforeSend: function() {
			$('select[name=\'country_id\']').after(' <i class="fa fa-circle-o-notch fa-spin"></i>');
		},
		complete: function() {
			$('.fa-spin').remove();
		},
		success: function(json) {
			if (json['postcode_required'] == '1') {
				$('input[name=\'postcode\']').parent().parent().addClass('required');
			} else {
				$('input[name=\'postcode\']').parent().parent().removeClass('required');
			}

			html = '<option value=""><?php echo $text_select; ?></option>';

			if (json['zone'] && json['zone'] != '') {
				for (i = 0; i < json['zone'].length; i++) {
					html += '<option value="' + json['zone'][i]['zone_id'] + '"';

					if (json['zone'][i]['zone_id'] == '<?php echo $zone_id; ?>') {
						html += ' selected="selected"';
					}

					html += '>' + json['zone'][i]['name'] + '</option>';
				}
			} else {
				html += '<option value="0" selected="selected"><?php echo $text_none; ?></option>';
			}

			$('select[name=\'zone_id\']').html(html);
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});

$('select[name=\'country_id\']').trigger('change');
//--></script>

        <!--frd 5-->
        <script type="text/javascript"><!--
        $('select[name=\'zone_id\']').on('change', function() {
        	$.ajax({
        		url: 'index.php?route=extension/total/shipping/zone&zone_id=' + this.value,
        		dataType: 'json',
        		beforeSend: function() {
        			$('select[name=\'zone_id\']').after(' <i class="fa fa-circle-o-notch fa-spin"></i>');
        		},
        		complete: function() {
        			$('.fa-spin').remove();
              $('select[name=\'district_id\']').trigger('change');
        		},
        		success: function(json) {

              html = '<option value=""><?php echo $text_select; ?></option>';

        			if (json['raoprop'] && json['raoprop']['rajaongkir']['results'] != '') {

        				for (i = 0; i < json['raoprop']['rajaongkir']['results'].length; i++) {
        					html += '<option value="' + json['raoprop']['rajaongkir']['results'][i]['city_id'] + '"';

        					if (json['raoprop']['rajaongkir']['results'][i]['city_id'] == '<?php echo $district_id; ?>') {
        						html += ' selected="selected"';
                  }

        			  		html += '>' + json['raoprop']['rajaongkir']['results'][i]['city_name'] + ' - ' + json['raoprop']['rajaongkir']['results'][i]['type'] + '</option>';
        				}
        			} else {
        				/*html += '<option value="0" selected="selected"><?php echo $text_none; ?></option>';*/
        			}
              $('select[name=\'district_id\']').html(html);
              $('select[name=\'subdistrict_id\']').html('<option value=""><?php echo $text_select; ?></option>');
        		},
        		error: function(xhr, ajaxOptions, thrownError) {
        			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        		}
        	});
        });
        $('select[name=\'zone_id\']').trigger('change');
        /*---*/
        $('select[name=\'district_id\']').on('change', function() {
          $.ajax({
            url: 'index.php?route=extension/total/shipping/district&district_id=' + this.value,
            dataType: 'json',
            beforeSend: function() {
              $('select[name=\'district_id\']').after(' <i class="fa fa-circle-o-notch fa-spin"></i>');
            },
            complete: function() {
              $('.fa-spin').remove();
            },
            success: function(json) {
              html = '<option value=""><?php echo $text_select; ?></option>';
              if (json['subdistricts'] && json['subdistricts']['rajaongkir']['results'] != '') {
                for (i = 0; i < json['subdistricts']['rajaongkir']['results'].length; i++) {
                  html += '<option value="' + json['subdistricts']['rajaongkir']['results'][i]['subdistrict_id'] + '"';
                  if (json['subdistricts']['rajaongkir']['results'][i]['subdistrict_id'] == '<?php echo $subdistrict_id; ?>') {
                    html += ' selected="selected"';
                  }
                  html += '>' + json['subdistricts']['rajaongkir']['results'][i]['subdistrict_name'] +  '</option>';
                }
              } else {
                /*html += '<option value="0" selected="selected"><?php echo $text_none; ?></option>';*/
              }

              $('select[name=\'subdistrict_id\']').html(html);
            },
            error: function(xhr, ajaxOptions, thrownError) {
              alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
          });
        });

        //--></script>


    </div>
  </div>
</div>
