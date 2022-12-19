
<script src="js/jquery-3.6.2.min.js"></script>

{if $loggedIn == 1}
<!-- TODO: remove the cachets in production -->
<script src="js/otpweb.js?ts={$cachets}"></script>
{/if}

{if $pwdfocus == 1}
    {literal}
        <script>
            $('#pwd').focus();
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