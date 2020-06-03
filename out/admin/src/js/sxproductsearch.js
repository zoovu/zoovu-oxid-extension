console.log('sxproductsearch.js loaded');


// check Status
sxCheckUploadStatus();
setInterval(sxCheckUploadStatus, 3000);


function sxCheckUploadStatus() {

    // get status overview
    var url = document.getElementById('sxUploadStatusUrl');
    url = url.content;

    var statusOverview = sxAjaxController(url);
    statusOverview = JSON.parse(statusOverview);

    // get languages
    var sxShops = document.getElementById("sxActiveShops");
    if (!sxShops) return;

    sxShops = JSON.parse(sxShops.content);

    for (i = 0; i < sxShops.length; i++) {

        //check shop status
        sxCheckShopStatus(sxShops[i], statusOverview.data);
    }

}


function sxCheckShopStatus(sxShop, statusOverview) {

    var sxShopId = document.getElementById("sxActiveShopId");
    if (!sxShopId) return;

    shopIdentifier = sxShopId.content + '-' + sxShop['oxid'];

    var sxStatusPending = document.getElementById("sxStatusPending" + sxShop['oxid']);
    var sxStatusProcessing = document.getElementById("sxStatusProcessing" + sxShop['oxid']);

    // hide everything of shop
    sxStatusPending.classList.add('sxHide');
    sxStatusProcessing.classList.add('sxHide');

    if (!statusOverview[shopIdentifier]) return;

    if (statusOverview[shopIdentifier]['phase'] != 'PENDING') {
        // shop currently processing
        // -> show progress

        // set percentage
        var percentageInt = statusOverview[shopIdentifier].totalPercentage;
        var percentage = percentageInt + '%';

        var statusBar = document.getElementById("sxBarStatus" + sxShop['oxid']);
        statusBar.style.width = percentage;

        var percentageView = document.getElementById("sxUploadPercentItem" + sxShop['oxid']);
        percentageView.innerHTML = percentage;

        if (percentageInt >= 95) {
            statusBar.classList.add('sx100');
        } else {
            statusBar.classList.remove('sx100');
        }

        sxStatusProcessing.classList.remove('sxHide');

    } else {
        // shop pending
        // -> show option to start

        sxStatusPending.classList.remove('sxHide');

    }

}


function sxAjaxControllerClick(url) {
    sxAjaxController(url);
    sxCheckUploadStatus();
}

function sxAjaxController(url) {
    
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.open("GET", url, false); // false for synchronous request
    xmlHttp.send(null);

    return xmlHttp.responseText;

}

