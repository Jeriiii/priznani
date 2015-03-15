window.fbAsyncInit=function(){ 
     FB.Event.subscribe("edge.create", function(a){ a=a.match(/[0-9]+/); $.get( {{$inc-like}} + "&id_confession="+a); });     
     FB.Event.subscribe("edge.remove", function(a){ a=a.match(/[0-9]+/)[0];$.get( {{$dec-like}} + "&id_confession="+a); });     
     FB.Event.subscribe("comment.create", function(a){ a=a.href.match(/[0-9]+/)[0];$.get( {{$inc-comment}} + "&id_confession="+a); });     
     FB.Event.subscribe("comment.remove", function(a){ a=a.href.match(/[0-9]+/)[0];$.get( {{$dec-comment}} + "&id_confession="+a); });     
};

 
////    window.fbAsyncInit = function() {
////                                }
//                                //FB.init({ appId: '639745929384551',channelUrl : '//www.milionovykouc.cz/chanel.html', status: true, cookie: true,xfbml: true});
//                                FB.Event.subscribe("edge.create", function(targetUrl) {
//                                        var id = targetUrl.match(/[0-9]+/);
//                                        $.get({{$inc-like}} + "&id_confession=" + id);
//                                });
//                                FB.Event.subscribe("edge.remove", function(targetUrl) {
//                                        var id = targetUrl.match(/[0-9]+/)[0];
//                                        $.get({{$dec-like}} + "&id_confession=" + id);
//                                });
//                                FB.Event.subscribe("comment.create", function(target) {
//                                        var href = target.href;
//                                        var id = href.match(/[0-9]+/)[0];
//                                        $.get({{$inc-comment}} + "&id_confession=" + id);
//                                });
//                                FB.Event.subscribe("comment.remove", function(target) {
//                                        var href = target.href;
//                                        var id = target.href.match(/[0-9]+/)[0];
//                                        $.get({{$dec-comment}} + "&id_confession=" + id);
//                                });
//                        };
//                        (function(d, s, id) {
//                                var js, fjs = d.getElementsByTagName(s)[0];
//                                if (d.getElementById(id)) return;
//                                js = d.createElement(s); js.id = id;
//                                js.src = "//connect.facebook.net/cs_CZ/all.js#xfbml=1&appId=639745929384551";
//                                fjs.parentNode.insertBefore(js, fjs);
//                        }(document, 'script', 'facebook-jssdk'));
//
//