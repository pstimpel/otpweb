{if sizeof($totpValues) > 0}
    {section name=m loop=$totpValues}
        {strip}
            <div class="tokenbackground" style="position:relative;">
                <div class="row flex-row otpdescription">
                    <div class="col-lg-1 col-md-1 col-sm-12 zeropadding" id="iconparent{$totpValues[m].totp_id}">
                        <a href="javascript:toggleIconWindow('{$totpValues[m].totp_id}', document.getElementById('iconparent{$totpValues[m].totp_id}'))">
                            <img id="icon{$totpValues[m].totp_id}" src="{$totpValues[m].totp_icon}" alt="icon" />
                        </a>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12 tokendiv"  >
                        <form name="store{$totpValues[m].totp_id}">
                            <input type="hidden" name="totp_iv_b64_{$totpValues[m].totp_id}" id="totp_iv_b64_{$totpValues[m].totp_id}" value="{$totpValues[m].totp_iv_b64}">
                            <input type="hidden" name="totp_id_{$totpValues[m].totp_id}" id="totp_id_{$totpValues[m].totp_id}" value="{$totpValues[m].totp_id}">

                            <div id="tokendescriptionshow{$totpValues[m].totp_id}" class="tokenshow">
                                <span class="tokentext" id="tokentext_{$totpValues[m].totp_id}">{$totpValues[m].totp_description}</span>
                                <span class="spanshow">
                                    <a href="javascript:toggledescription('{$totpValues[m].totp_id}');">
                                        <i class="fa fa-edit menutopic"></i>
                                    </a>
                                </span>
                            </div>
                            <div id="tokendescriptionedit{$totpValues[m].totp_id}" class="tokenedit">
                                <span class="spaneditform">
                                    <input class="form-control spaneditinput" type="text" name="totp_description_{$totpValues[m].totp_id}" id="totp_description_{$totpValues[m].totp_id}" value="{$totpValues[m].totp_description}">
                                </span>
                                <span class="spaneditsave">
                                    <a href="javascript:savedescription('{$totpValues[m].totp_id}');">
                                        <i class="fa fa-save menutopic"></i>
                                    </a>
                                </span>
                                <span class="spanshow">
                                    <a href="javascript:toggledescription('{$totpValues[m].totp_id}');">
                                        <i class="fa fa-edit menutopic"></i>
                                    </a>
                                </span>
                            </div>
                        </form>
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-12 tokendiv" >
                        <span class="tokentext">{$totpValues[m].totp_ts_hr}</span>
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-12 otptoken tokendiv" >
                        <div id="token{$totpValues[m].totp_id}">
                            <span class="textaligncenter tokentext">
                                <a href="javascript:reveal('token{$totpValues[m].totp_id}','{$totpValues[m].totp_id}')"><i class="fa fa-refresh menutopic"></i></a>
                            </span>
                        </div>
                    </div>
                    <div class="col-lg-1 col-md-1 col-sm-12 otptoken tokendiv" >
                        <span class="tokentext">
                            <a href="index.php?action=delete&id={$totpValues[m].totp_id}"><i class="fa fa-trash menutopic"></i></a>
                        </span>
                    </div>

                </div>
            </div>
            <div id="iconwindow{$totpValues[m].totp_id}" class="iconwindow">&nbsp;</div>
            <div class="row flex-row" >
                <div class="col-lg-3 col-sm-12 tokenouterspace">
                    &nbsp;
                </div>

            </div>

        {/strip}
    {/section}
{else}
    <div class="row flex-row" >
        <div class="col-lg-12 col-sm-12" >
            <h4>No TOTP Consumers set yet.</h4>

        </div>

    </div>
{/if}

