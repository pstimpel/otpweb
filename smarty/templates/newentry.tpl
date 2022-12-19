<div class="row flex-row" >
    <div class="col-lg-12 col-sm-12" >
        <h4>New entry</h4>
        <form name="new" method="post" action="index.php?action=storeentry">

            <div class="form-group">
                <label for="description">Name of TOTP consumer</label>
                <input type="text" class="form-control" maxlength="{$DESCRIPTION_LENGTH}" name="description" id="description" placeholder="Name of TOTP consumer">
            </div>

            <div class="form-group">
                <label for="secret">Secret received from TOTP consumer</label>
                <input type="text" class="form-control" maxlength="{$TOTP_SECRET_LENGTH}" name="secret" id="secret" placeholder="Secret received from TOTP consumer">
            </div>

            <button type="submit" class="btn btn-primary">Save</button>

        </form>

    </div>
</div>
