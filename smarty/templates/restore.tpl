<div class="row flex-row" >
    <form action="index.php?action=restoreupload" method="post" enctype="multipart/form-data">
        <div class="col-lg-12 col-sm-12 " >
            <h4 id="willbeoverwritten" style="visibility: hidden;">Attention: existing data will be overwritten!</h4>
        </div>

        <div class="col-lg-12 col-sm-12 " >
            <div class="form-group">
                <label for="restorefile">File to restore from (json):</label>
                <input type="file" name="file" class="form-control-file" id="restorefile"/>
            </div>
        </div>

        <div class="col-lg-12 col-sm-12 " >
            <div class="form-group" style="padding: 21px;">

                <input class="form-check-input" type="checkbox" name="overwritedb" value="yes" id="overwritedb" onChange="toggleDbOverwrite();">
                <label for="overwritedb">Delete Database before restoring data</label>
            </div>

        </div>

        <div class="col-lg-12 col-sm-12" style="padding-top: 20px;">

            <button type="submit" class="btn btn-primary">Restore</button>

        </div>
    </form>
</div>