$(document).ready(function(){
/*
	// AJAX Rate
	$("ul.rating li a, ul.rated li a").click(function(event){
		event.preventDefault();

		// Get the child img id which holds some info we need.
		var child = $(this).children().attr('id');

		// Child contains the object_id and the star number.
		var object = child.split('_');

		// Set the red stars that we neeed to.
		for (i = 1; i <= max_rating; i++)
		{
			if (i <= object[1])
			{
				$('#' + object[0] + '_' + i).attr({src: green_star.src});
			}
			else
			{
				$('#' + object[0] + '_' + i).attr({src: grey_star.src});
			}
		}

		if ($("#rating_" + object[0]).hasClass("rating"))
		{
			$("#rating_" + object[0]).removeClass("rating");
			$("#rating_" + object[0]).addClass("rated");
			$(this).unbind("hover");
			$("#" + object[0] + "_remove").parent().parent().removeClass("hidden");
		}
		else
		{
			$("#rating_" + object[0]).removeClass("rated");
			$("#rating_" + object[0]).addClass("rating");
			$("#" + object[0] + "_remove").parent().parent().addClass("hidden");
		}

		return;

		$.ajax({
			type: "POST",
			url: $(this).attr('href'),
			success: function() {
			}
		});
	});
*/
	// Rating hover functions
	$("ul.rating li a").hover(
		function(){
			// Over function. This will change the stars up to the point that was hovered on to red

			// Get the child img id which holds some info we need.
			var child = $(this).children().attr('id');

			// Child contains the object_id and the star number.
			var object = child.split('_');

			$('#rating' + object[0] + " li a img").each(function() {
				$(this).attr({src: grey_star.src});
			});

			// Set the red stars that we neeed to.
			for (i = 1; i <= object[1]; i++)
			{
				$('#' + object[0] + '_' + i).attr({src: red_star.src});
			}
		},
		function(){
			// Out function. Reset to default stars
			$("ul.rating li a img.green").each(function() {
				$(this).attr({src: green_star.src});
			});

			$("ul.rating li a img.orange").each(function() {
				$(this).attr({src: orange_star.src});
			});

			$("ul.rating li a img.grey").each(function() {
				$(this).attr({src: grey_star.src});
			});
		}
	);
});