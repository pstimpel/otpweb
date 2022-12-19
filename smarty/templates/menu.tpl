<nav class="navbar navbar-expand-lg navbar-light bg-light">

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">

        <ul class="nav nav-tabs">

            <li class="nav-item">
                <a class="nav-link" href="index.php" ><i class="fa fa-home menutopic"></i></a>

            </li>

            {if $loggedIn == 1}

                <li class="nav-item">
                    <a class="nav-link" href="?action=new" ><i class="fa fa-plus-circle menutopic"></i></a>
                </li>

                <li class="nav-item">
                    <div id="clock" class="clock"></div>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="javascript:refreshsession()"><i class="fa fa-refresh menutopic"></i></a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="?action=showicons" ><i class="fa fa-file-image-o menutopic"></i></a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="?action=backup" target="_blank" ><i class="fa fa-cloud-download menutopic"></i></a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="?action=restore" ><i class="fa fa-cloud-upload menutopic"></i></a>
                </li>

                <li class="nav-item">
                    <div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="index.php?action=logoff"><i class="fa fa-lock menutopic"></i></a>
                </li>

            {/if}

            <li class="nav-item">
                <div id="version" class="clock">V {$OTPVERSION}</div>
            </li>

            {if $loggedIn == 1}

                <li class="nav-item">
                    <div id="pwcheck" class="clock"></div>
                </li>

            {/if}

        </ul>

    </div>

</nav>
