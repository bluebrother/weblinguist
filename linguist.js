
function toggleStatus(element)
{
    var s = document.getElementsByName("status-" + element)[0].value;
    if(s == "unfinished") {
        document.getElementsByName("status-" + element)[0].value = "finished";
        document.getElementsByName("istatus-" + element)[0].src = "dialog-information.png";
        document.getElementsByName("sstatus-" + element)[0].childNodes[1].data = "finished";
        document.getElementsByName("tstatus-" + element)[0].firstChild.nodeValue = "(mark unfinished)";
    }
    else {
        document.getElementsByName("status-" + element)[0].value = "unfinished";
        document.getElementsByName("istatus-" + element)[0].src = "dialog-warning.png";
        document.getElementsByName("sstatus-" + element)[0].childNodes[1].data = "unfinished";
        document.getElementsByName("tstatus-" + element)[0].firstChild.nodeValue = "(mark finished)";
    }
}

