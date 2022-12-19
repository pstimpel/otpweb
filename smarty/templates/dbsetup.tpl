<div class="row flex-row" >
    <div class="col-lg-12 col-sm-12" >
        <h1>There is no database configuration available, please create one!</h1>


    </div>

</div>
<div class="row flex-row" >
    <div class="col-lg-12 col-sm-12" >
        <p>The DB server has to be reachable from the web app, the database and the username must exist, and the user must be allowed to create tables, select, update and delete datarows</p>
        <form name="dbsetup" method="post" action="index.php?action=dbsetup">
            <div class="form-group">
                <label for="dbserver">PostgreSQL Server</label>
                <input type="text" class="form-control" name="dbserver" id="dbserver" placeholder="PostgreSQL Server">

            </div>
            <div class="form-group">
                <label for="dbport">PostgreSQL TCP Port</label>
                <input type="text" class="form-control" name="dbport" id="dbport" placeholder="5432">

            </div>
            <div class="form-group">
                <label for="dbname">PostgreSQL Database Name</label>
                <input type="text" class="form-control" name="dbname" id="dbname" placeholder="PostgreSQL Database Name">

            </div>
            <div class="form-group">
                <label for="dbuser">PostgreSQL Username</label>
                <input type="text" class="form-control" name="dbuser" id="dbuser" placeholder="PostgreSQL Username">

            </div>
            <div class="form-group">
                <label for="dbpass">Password</label>
                <input type="password" class="form-control" name="dbpass" id="dbpass" placeholder="Password">
            </div>

            <button type="submit" class="btn btn-primary">Create Configuration</button>

        </form>
    </div>

</div>