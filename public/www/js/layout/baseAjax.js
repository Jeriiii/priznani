				jQuery.ajaxSetup({ cache:!1,dataType:"json",success:function(a){ if(a.snippets)for(var b in a.snippets)$("#"+b).html(a.snippets[b]);}});$(".polly, .add-to-fb-page").live("click",function(a){ a.preventDefault();a=$(a.target);void 0===a.attr("data-action")&&($.get(this.href),a.attr("data-action","clicked"));});var ordermenu=$("#ordermenu"),orderlink=$("#orderlink");orderlink.click(function(){ ordermenu.stop().animate({ opacity:"toggle"},500);});
	$("body").click(function(a){ !$(a.target).parents().is("#orders")&&ordermenu.is(":visible")&&orderlink.trigger('click');});var container=$("#left-side"),panel=$("#left-panel"),label=$("#left-panel-label"),leftspeed=200,isappearing=!1,isdisappearing=!1;container.mouseenter(function(){ isappearing||(isappearing=!0,isdisappearing=!1,label.stop().animate({ left:panel.width()},leftspeed,"linear"));});
	container.mouseleave(function(){ isdisappearing||(isappearing=!1,isdisappearing=!0,label.stop().animate({ left:-1*$("#left-panel-label").width()+$("#left-panel").width()},leftspeed,"linear"));});$("#left-side .first, #left-side .second, #left-side .third, #left-side .fourth,#left-side .fifth,#left-side .sixth, #left-side .thirdparty").hover(function(){ $("."+$(this).attr("class")).css("text-decoration","underline");},function(){ $("."+$(this).attr("class")).css("text-decoration","none");});
					
	//////////////////////////////AJAX///////////////////////////////////
//            jQuery.ajaxSetup({
//                    cache: false,
//                    dataType: 'json',
//                    success: function (payload) {
//                            if (payload.snippets) {
//                                    for (var i in payload.snippets) {
//                                            $('#' + i).html(payload.snippets[i]);
//
//                                    }
//                            }
//                    }
//            });
//            $(".polly, .add-to-fb-page").live('click', function (e) { //click(function(e) {
//                    e.preventDefault();
//                    var target = $(e.target);
//                    var dataAction = target.attr("data-action");
//                    if(dataAction === undefined){
//                            $.get(this.href);
//                            target.attr("data-action", "clicked");
//                    }
//            });
				