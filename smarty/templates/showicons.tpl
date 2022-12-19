<div class="row flex-row" >
    <div class="col-lg-12 col-sm-12" >
        {if isset($uploadresult)}
            <h4>{$uploadresult}</h4>
        {/if}
        <form action="index.php?action=picupload" method="post" enctype="multipart/form-data">
            <label for="fileAvatar">Filename (min 64x64px, square):</label>
            <input type="file" name="file" id="fileAvatar"/>
            <br/>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>

</div>
<div class="col-lg-12 col-sm-12" style="height:20px;">
</div>
<div class="row flex-row" >
    <div class="col-lg-12 col-sm-12" >
        {if sizeof($icons) > 0}
            {section name=m loop=$icons}
                {strip}
                    <a href="index.php?action=showicon&id={$icons[m]}"><img src="{$icondirectory}{$icons[m]}" alt="icon" /></a>&nbsp;
                {/strip}
            {/section}
        {/if}
    </div>
</div>