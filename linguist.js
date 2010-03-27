
function toggleStatus(element)
{
    var s = document.getElementById("status-" + element).value;
    if(s == "unfinished") {
        document.getElementById("status-" + element).value = "finished";
        document.getElementById("istatus-" + element).src = "dialog-information.png";
        document.getElementById("sstatus-" + element).childNodes[1].data = "finished";
        document.getElementById("tstatus-" + element).firstChild.nodeValue = "(mark unfinished)";
    }
    else {
        document.getElementById("status-" + element).value = "unfinished";
        document.getElementById("istatus-" + element).src = "dialog-warning.png";
        document.getElementById("sstatus-" + element).childNodes[1].data = "unfinished";
        document.getElementById("tstatus-" + element).firstChild.nodeValue = "(mark finished)";
    }
}

