var defaultCenter = {lat: 30.1, lng: -90.3};
var defaultZoom = 8;
var map;
var infowindow;
var markers = [];


(function($){
	$( document ).ready(function() {
		$("#parish").change(function() {
			var parish = $(this).val();
			$("#street").empty();
			$("#subdivision").empty();
			$("#district").empty();

			$.ajax({
				type: "POST",
				url: ajaxurl,
				dataType: "json",
				data: {
					"action": "get_parish_data",
					"parish": parish,
					"security": ajax_nonce, // Nonce
				},
				success: function( data ) {
					if (data["streets"])
						$("#street").append(data["streets"]);
					if (data["subdivisions"])
						$("#subdivision").append(data["subdivisions"]);
					if (data["districts"])
						$("#district").append(data["districts"]);
				}
			})
		});

		$('.select2').select2({
			closeOnSelect: false
		});
	
	});


}(jQuery));