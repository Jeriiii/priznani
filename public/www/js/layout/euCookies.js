$('.fucking-eu-cookies a.fucking-eu-button').click(function() {
    var date = new Date();
    date.setFullYear(date.getFullYear() + 10);
    document.cookie = 'fucking-eu-cookies=1; path=/; expires=' + date.toGMTString();
    $('.fucking-eu-cookies').hide();
});