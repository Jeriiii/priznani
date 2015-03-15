$(document).ready(function() {
				var check = "Check ";
				
				function inc(element) {
					var number = $(element).children();
					$.each(number, function(i,v) {
						var count = parseInt($(this).text());
						count++;
						$(this).text(count);
					});
				}
				
				function dec(element) {
					var number = $(element).children();
					$.each(number, function(i,v) {
						var count = parseInt($(this).text());
						count--;
						$(this).text(count);
					});
				}
				
				function checkIt(element) { 
					var text = $(element).text();
					element.text(check + text);
				}
				
				function uncheckIt(element) {
					var text = $(element).text().replace(check, "");
					element.text(text);
				}
				
				function choose(element) {
					var attr = element.attr("data-polly");
					if(attr.length == 4) /* real nebo fake */ {
						inc(element);
						checkIt(element);
					}else{ /* neco - neco */
						$array_date_polly = attr.split("-");
						if($array_date_polly[0].charAt(0) == 'r'){
							if($array_date_polly[1].charAt(0) == 'r'){ /* real-real */
								dec(element);
								uncheckIt(element);
							}else if($array_date_polly[1].charAt(0) == 'f'){ /* real-fake */
								var polls = $(this).parent().children();
								//var array_polls;
								var index = 0;
								$.each(polls, function(i, v){
									if(index == 0){
										dec(v);uncheckIt(v);
									}else if(index == 1){
										inc(v);checkIt(v);
									}
									++index;
								});
							}
						}else if($array_date_polly[0].charAt(0) == 'f'){
							if($array_date_polly[1].charAt(0) == 'r'){ /* fake-real */
								var polls = $(this).parent().children();
								var index = 0;
								$.each(polls, function(i, v){
									if(index == 0){
										inc(v);checkIt(v);
									}else if(index == 1){
										dec(v);uncheckIt(v);
									}
									++index;
								});
//								var array_polls;
//								var index = 0;
//								$.each(polls, function(i, v){
//									array_polls[index] = v;
//									++index;
//								});
//								inc(array_polls[0]);checkIt(array_polls[0]);
//								dec(array_polls[1]);uncheckIt(array_polls[1]);
							}else if($array_date_polly[1].charAt(0) == 'f'){ /* fake-fake */
								dec(element);
								uncheckIt(element);
							}
						}
					}
				}
				$(".polly").click(function(e) {
					e.preventDefault();
					choose($(this));
					$.get(this.href);
				});
				
				
			});


