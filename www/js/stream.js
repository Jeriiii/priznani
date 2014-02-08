/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function(){
    var load = 3;
    $(window).scroll(function(){
        
        /* pokud uzivatel naskroluje nakonec stranky tak if projde */
       if($(window).scrollTop() === ($(document).height() - $(window).height()) ) 
         //  load++;
         alert('jste nakonci');
       //    $(document).getElementById('stream-btn-next').href("getMoreData! 'offset'=>"+load);
           // $('#next-data-item-btn').href({link getMoreData!});
//        $.get({link getMoreData!} "&offset=" + load);
        //$.get({link decComment!} + "&id_confession=" + id);
        });
    
});
