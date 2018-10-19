//导航高亮
function highlight_subnav(url) {
    var $_nav, $subnav = $("#nav-accordion"), _txt;

    $_nav = $subnav.find("a[href='" + url + "']");
    console.log($_nav);
    $that = $_nav.parent();
    $that.addClass("active");
    /*导航*/
    _txt = $_nav.text();
    if (!_txt) return false;
    console.log( $that.parent('.sub').css('display'));
    $that.parent('.sub').css({"display":"block",'overflow':'hidden'}) ;
    var ftmpon = $that.parent().parent('.sub-menu').find('a.dcjq-parent');
    ftmpon.addClass("active");


}