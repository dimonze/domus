$(document).ready(function() {
	$(window).scroll(function() {
		scrollBanners();
	});
	scrollBanners();
});

function scrollBanners() {
	var height = $(window).height();
	$(window).resize(function(){
		height = $(window).height();
	});
	var header = $('#result').offset().top;
	var result = $('#result').outerHeight();
	var scroll = $(window).scrollTop();
	var L2 = header + result - height;
	
	
	var blocks = Array();
	blocks[0] = new Object();
	blocks[0].block = $('#greyBlock');
	blocks[0].height = blocks[0].block.outerHeight();
	blocks[0].topHeight = $('.house-tabs').outerHeight() + $('.boxBack_04').outerHeight() + $('.boxBack_05').outerHeight() + $('#banner-search-left').outerHeight() + $('#searchGreyBlock').outerHeight() + 28;
	blocks[0].width = '148px';

	blocks[1] = new Object();
	blocks[1].block = $('.aside-adv-box');
	blocks[1].height = blocks[1].block.outerHeight();
	blocks[1].topHeight = $('#banner-search-right').outerHeight() + $('.searchMap').outerHeight() + $('.searchAddr-wrap').outerHeight() + 20;
	blocks[1].width = '298px';
	
	for( var i in blocks)
	{
		var L1 = header + blocks[i].topHeight;
		var max_margin = result - blocks[i].topHeight - blocks[i].height;
		var delimiter = (blocks[i].height - height)/(L2 - L1);
		if ($('#result').outerHeight() >= height + blocks[i].topHeight)  
		{
			if (scroll >= L2) 
			{
				blocks[i].block.css('position', 'relative');
				blocks[i].block.css('margin-top', max_margin);
				blocks[i].block.css('top', '0px');
			}
			else if (scroll >= L1) 
			{
				blocks[i].block.css('position', 'fixed');
				var mt = (scroll - L1) * delimiter;
				blocks[i].block.css('top', - mt);
				blocks[i].block.css('margin-top', '0px');
			}
			else 
			{
				blocks[i].block.css('margin-top', '10px');
				blocks[i].block.css('top', '0px');
				blocks[i].block.css('position', 'relative');
			}
			blocks[i].block.css('width', blocks[i].width);
		}
	}
}