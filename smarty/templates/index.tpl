{include file="head.tpl"}

{include file="menu.tpl"}


<div id="content_container" style="margin: 10px;">

    <div id="container">
        <div style="height: 10px;"></div>

        <noscript>
            <div class="noscript">
                <span style="font-size:14pt; font-weight: bold; margin: 30px auto; display: block;">We are sorry, but this page requires JavaScript!</span>
                <span>Please check out <a
                            href="http://www.activatejavascript.org">www.activatejavascript.org</a> for detailed instruction on how to enable JavaScript in your browser.</span>
            </div>
        </noscript>

        {if isset($template) and $template ne ""}

            {include file=$template}

        {else}
            {include file="welcome.tpl"}
        {/if}
    </div>


    {include file="foot.tpl"}
