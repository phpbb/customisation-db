$(document).ready(function() {
	var iframe_height = $('html').outerHeight() - $('.demo-header').outerHeight();
	default_search = $('input[name="q"]').attr('value');
	
	$('iframe').height(iframe_height);
	$('.style-select').removeClass('simple');
	
	preload_images();
});

$('input[name="q"]').focus(function() {
	if ($(this).attr('value') == default_search)
	{
		$(this).attr('value', '');
	}
});

$('input[name="q"]').search('.demo-list ul li:not(.category)', function(on) {
	on.reset(function() {
		$('#none').hide();
		$('.demo-list ul li').show();
	});

	on.empty(function() {
		$('#none').show();
		$('.demo-list ul li:not(.category)').hide();
	});

	on.results(function(results) {
		$('#none').hide();
		$('.demo-list ul li:not(.category)').hide();
		results.show();
	});
});

$('.demo-list li:not(.category)').hover(function() {
	cid = '#demo-data .'+ $(this).attr('class');
		
	if ($('.viewport div').html() == $(cid +' .preview').html())
	{
		return;
	}

	var preview = $(cid+' .preview').html();
	$('.viewport div').stop(true, true).hide();
	$('.viewport div').html(preview);
	$('.viewport div').fadeIn(200);
});

function preload_images()
{
	$('#demo-data li').each(function() { 
		var cid = '#demo-data .'+$(this).attr('class');
		load_image(cid);
	});
}

function load_image(cid)
{
	if (!$(cid+' .preview img').attr('src') && $(cid).length)
	{
		var img_src = $(cid+ ' .source').html();
		$(cid+' .preview img').attr('src', img_src);
	}
}