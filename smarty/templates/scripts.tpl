
<script src="js/jquery-3.6.2.min.js"></script>

<script src="js/notifit.js" type="text/javascript"></script>



{literal}
<script>

    function copyToClipboard(elementname) {
        let element = document.getElementById(elementname); // Element mit Namen elementname holen

        if (!element) {
            console.error("Element mit dem Namen "+elementname+" nicht gefunden.");
            return;
        }

        let text = element.innerText || element.textContent; // Inhalt holen

        if (navigator.clipboard && window.isSecureContext) {
            // Moderne Methode (nur über HTTPS verfügbar)
            navigator.clipboard.writeText(text).then(() => {
                console.log("Inhalt erfolgreich kopiert!");
                notifyUser('info', 'Copied!', 5000, true, true, true);
            }).catch(err => {
                notifyUser('error', 'Copy failed', 5000, true, true, true);
            });
        } else {
            // Fallback für ältere Browser
            let textarea = document.createElement("textarea");
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand("copy");
                notifyUser('info', 'Copied (old school)!', 5000, true, true, true);
            } catch (err) {
                notifyUser('error', 'Copy failed (old school)!', 5000, true, true, true);
            }
            document.body.removeChild(textarea);
        }
    }

    /*
    * type:
    * success
    * error
    * warning
    * info
    *
    * timeout in millisecs
    * */
    function notifyUser(theType, theMessage, theTimeout, fade, multiline, autohide){
        notif({
            type: theType,
            msg: theMessage,
            position: "right",
            opacity: 0.8,
            timeout: theTimeout,
            bgcolor: "lightgrey",
            color: "black",
            fade: fade,
            multiline: multiline,
            autohide: autohide,
            width: 300
        });
    }
</script>
{/literal}

{if $loggedIn == 1}

    <script src="js/otpweb.js?ts={$OTPVERSION}"></script>
{/if}

{if strlen($notifymessage)>0}
    <script>
        notifyUser('info', '{$notifymessage}', 5000, true, true, true);
    </script>
{/if}

{if $pwdfocus == 1}
    {literal}
        <script>
            $('#'+'{/literal}{$passwordrelation}{literal}').focus();

            // TODO: this is such a dirty hack to avoid formfill suggestions by the browser, but seems to be working
            let passwordfield = document.getElementById('{/literal}{$passwordrelation}{literal}');

            passwordfield.onkeyup = function(e){
                if(e.keyCode == 13){
                    return false;
                } else {
                    if (passwordfield.value==null) {
                        passwordfield.type = 'text';
                    } else {
                        passwordfield.type = 'password';
                    }
                }
            }
        </script>
    {/literal}
{/if}


{if $loggedIn == 1 && $runHIBPcheck==1}
    {literal}
        <script>
            $.ajax({
                'async': true,
                'global': false,
                'url': 'index.php?action=checkpassword',
                success: function (res) {


                    //console.log(res);
                    switch (res) {
                        case "200":
                            $('#pwcheck').html('HIBP: passed');
                            break;
                        case "500":
                            $('#pwcheck').html('HIBP: failed');
                            break;
                        case "600":
                            $('#pwcheck').html('HIBP: skipped');
                            break;
                        default:
                            $('#pwcheck').html('HIBP: <b>Password found</b>');
                    }


                }
            });
        </script>
    {/literal}
{/if}


{if $loggedIn == 1}
{literal}
    <script>
        counter=0;
        counterrestart=false;
        function clock() {
            if(window.counterrestart==true) {
                window.counter=0;
                window.counterrestart=false;
            }
            if(window.counter == 0) {
                window.counter=0;
                $.ajax({
                    'async': true,
                    'global': false,
                    'url': 'index.php?action=getclock',
                    success: function (res) {

                        if (!isNaN(res)) {
                            $('#clock').html(res);
                        } else {
                            location.reload();
                        }
                    }
                });
            } else {
                let html = document.getElementById('clock').innerHTML;
                if(!isNaN(html)) {
                    if(html > 0) {
                        html = html - 1;
                        $('#clock').html(html);
                    } else {
                        window.counter=-1;
                    }
                }
                if(window.counter > 32) {
                    window.counter=-1;
                }
             }
            window.counter = window.counter + 1
            setTimeout("clock()",1000);
        }
        clock();

    </script>
{/literal}
{/if}