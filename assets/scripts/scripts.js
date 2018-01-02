$ = jQuery;
$(document).ready(function(){
    //OPEN MENU
    $('#menu_toggle').click(function(){
        $(this).toggleClass('open');
        $('.main-menu-list').toggleClass('open-menu');
        $('#navigation-panel').toggleClass('open-panel');
    });
});