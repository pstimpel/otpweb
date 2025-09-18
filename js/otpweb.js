

function getOffset(el) {
    const rect = el.getBoundingClientRect();
    return {
        left: rect.left + window.scrollX,
        top: rect.top + window.scrollY
    };
}

function selectIcon(divid, iconpath, icon) {
    $.ajax({
        'async': true,
        'global': false,
        'url': 'index.php?action=updateicon&id='+divid+"&icon="+icon,
        success: function(res) {
            //console.log(res);
            if(!isNaN(res)) {

                document.getElementById('iconwindow'+divid).style.visibility="hidden";
                document.getElementById('icon'+divid).src = iconpath;
                notifyUser('info', 'Icon changed', 5000, true, true, true);
            } else {
                location.reload();
            }
        }
    });
}

function toggleIconWindow(divid, sourcelemenet) {

    const rect = getOffset(sourcelemenet);

    $("#iconwindow"+divid).css({'top' : rect.top + 'px', 'left' : 150 + 'px'});


    if(document.getElementById("iconwindow"+divid).style.visibility=="hidden") {

        const elements1 = document.querySelectorAll(`[id^="iconwindow"]`);
        elements1.forEach(element => {
            document.getElementById(element.id).style.visibility="hidden";
        });

        $.ajax({
            'async': true,
            'global': false,
            'url': 'index.php?action=geticonwindowhtml&divid='+divid,
            success: function(res) {
                $("#iconwindow"+divid).html(res);
            }
        });


        document.getElementById("iconwindow"+divid).style.visibility="visible";
    } else {
        document.getElementById("iconwindow"+divid).style.visibility="hidden";
    }

}

function unreveal(divid, tokenid) {
    let html = "<span class=\"textaligncenter tokentext tokentext\"><a href=\"javascript:reveal('token"+tokenid+"','"+tokenid+"')\"><i class=\"fa fa-refresh menutopic\"></i></a></span>"

    document.getElementById(divid).innerHTML=html;
}

function unrevealqr(divid, tokenid) {

    var $div = $('#' + divid);
    $div.css('display', 'none');   // wieder verstecken
    $div.empty();

}

function savedescription(divid) {
    document.getElementById("tokendescriptionedit"+divid).style.visibility="hidden";
    document.getElementById("tokendescriptionshow"+divid).style.visibility="hidden";
    $.ajax({
        type: "POST",
        url: 'index.php?action=storedescription',
        data:{
            totp_id: document.getElementById('totp_id_' + divid).value,
            totp_iv_b64: document.getElementById('totp_iv_b64_' + divid).value,
            totp_description: document.getElementById('totp_description_' + divid).value
        },
        success: function(res){
            //success code here
            document.getElementById("tokendescriptionshow"+divid).style.visibility="visible";
            if(!isNaN(res)) {
                window.counterrestart=true;
                $('#tokentext_'+divid).html(document.getElementById('totp_description_' + divid).value);
                notifyUser('info', 'Description changed', 5000, true, true, true);
            } else {
                $('#tokentext_'+divid).html("Session timed out");
                location.reload();
            }

        },
        error: function(){
            document.getElementById("tokendescriptionshow"+divid).style.visibility="visible";
            document.getElementById("tokendescriptionshow"+divid).html("error");
        }
    });

}

function toggledescription(divid) {
    if(document.getElementById("tokendescriptionshow"+divid).style.visibility=="hidden") {
        document.getElementById("tokendescriptionedit"+divid).style.visibility="hidden";
        document.getElementById("tokendescriptionshow"+divid).style.visibility="visible";
    } else {
        document.getElementById("tokendescriptionshow"+divid).style.visibility="hidden";
        document.getElementById("tokendescriptionedit"+divid).style.visibility="visible";
    }

}

function toggleDbOverwrite() {

    if(!document.getElementById('overwritedb').checked) {
        document.getElementById("willbeoverwritten").style.visibility="hidden";
    } else {
        document.getElementById("willbeoverwritten").style.visibility="visible";
    }

}

function refreshsession() {
    $.ajax({
        'async': true,
        'global': false,
        'url': 'index.php?action=refreshsession',
        success: function(res) {
            //console.log(res);
            if(!isNaN(res)) {
                window.counterrestart=true;
            } else {

                location.reload();
            }
        }
    });
}

function reveal(divid, tokenid) {
    $('#'+divid).html("loading ...");
    $.ajax({
        'async': true,
        'global': false,
        'url': 'index.php?action=gettokenbyid&id='+tokenid,
        success: function(res) {
            //console.log(res);
            if(!isNaN(res)) {
                window.counterrestart=true;
                let myhtml = "<span id=\"otp-"+divid+"\" class=\"textaligncenter tokentext tokentext textbold\">"+res+"</span><br><a href=\"javascript:copyToClipboard('otp-"+divid+"');\"><i class=\"fa fa-copy menutopic\"></a>";
                //console.log(myhtml);
                $('#'+divid).html(myhtml);
            } else {
                $('#'+divid).html("Session timed out");
                location.reload();
            }
        }
    });


    setTimeout("unreveal('"+divid+"','"+tokenid+"')",10000);
}

function revealqr(divid, tokenid) {
    var $div = $('#' + divid);
    $div.html("loading ...");

    $.ajax({
        async: true,
        global: false,
        url: 'index.php?action=getqrbyid&id=' + tokenid,
        xhrFields: {
            responseType: 'blob'   // wichtig: damit Browser das als Bilddaten behandelt
        },
        success: function(res) {
            // Blob → ObjectURL → <img>
            var url = URL.createObjectURL(res);
            var img = $('<img>', {
                src: url,
                style: 'width:100%;height:100%;object-fit:contain;'
            });
            $div.html(img);
            $div.css('display', 'block');   // sichtbar machen
        }
    });


    setTimeout("unrevealqr('"+divid+"','"+tokenid+"')",10000);
}