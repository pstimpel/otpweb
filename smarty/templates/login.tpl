

<div class="row flex-row" >
    <div class="col-lg-12 col-sm-12" >
        <h4>Password, please</h4>
        <h5>Attention: All database entries are encrypted and decrypted using this password. Therefore you need to use the same password you were using when adding your TOTP entries to the database!</h5>
        <h5>In case your forget your password, there is no way to recover the data from the database. </h5>
        <form name="login" method="post" action="index.php?action=login">
            <div class="form-group" class="tokenouterspace">
                <label for="pwd">Password</label>
                <input type="password" class="form-control" name="pwd" id="pwd" placeholder="Password">
            </div>

            <button type="submit" class="btn btn-primary">Login</button>

        </form>

    </div>
</div>
