	var imgObj = new Image();
	$(imgObj).insertBefore($(".loading"));
	$("*").each(function(){
		if($(this).attr('data-singleimg')){
			
			var self = this;
			
			$(self).unbind('click').bind('click',function(){
				
				$(imgObj).hide();
				
				$(".loading").show();
				
				imgObj.src = $(this).attr('data-singleimg');
				
				var phonediog = $("#phonediog");
				
				phonediog.removeClass('hide');
				
				var Index = $(this);
				
				imgObj.onload = function (){
					$(imgObj).show();
					
					$(".loading").hide();
				}
				phonediog.unbind('click').bind('click',function(){
					
					$(this).addClass('hide');
					
				});
				
				
				//Index.
			});
		}
	});
	
	
	
	
		
	
	
	
	

