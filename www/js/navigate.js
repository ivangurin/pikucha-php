//document.onkeydown = NavigateThrough;

function NavigateThrough(){

    var link = 0;

    switch (event.keyCode ? event.keyCode : event.which ? event.which : null){
        case 0x25:
            link = document.getElementById('link_left');
            break;
        case 0x26:
            link = document.getElementById('link_up');
            break;
        case 0x27:
            link = document.getElementById('link_right');
            break;
        case 0x28:
            link = document.getElementById('link_down');
            break;
    }

    if (link && link.href) document.location = link.href;

}