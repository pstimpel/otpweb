<?php

class Db
{
    /**
     * Deletes all totp entries from database
     *
     * @return bool True if success, otherwise false
     */
    public static function deleteAllEntries(): bool
    {
        global $db;
        $sql = "delete from totp";
        $stmt = $db->prepare($sql);
        if (!$stmt->execute()) {
            print_r($stmt->errorInfo());
            return false;
        } else {
            return true;
        }
    }

    /**
     * Deletes a single entry from database
     *
     * @param int $id id of the entry
     *
     * @return bool True if success, otherwise false
     */
    public static function deleteEntryById(int $id): bool
    {
        global $db;
        $sql = "delete from totp where totp_id=?";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(1, $id);
        if (!$stmt->execute()) {
            print_r($stmt->errorInfo());
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get a list of OTP token entries from database, specified by $id
     *
     * @param int $id id of the entry, could be 0 for all entries
     *
     * @return array Array of token entries, sorted by description
     *
     */
    public static function getOtpValues(int $id): array
    {
        global $db;
        $icons=Otp::getIcons();
        $sql = "select * from totp where totp_id <> ?";
        if ($id > 0) {
            $sql = "select * from totp where totp_id = ?";
        }

        $stmt = $db->prepare($sql);
        $stmt->bindValue(1, $id);

        if (!$stmt->execute()) print_r($stmt->errorInfo());
        $totpValues = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $tokenSecret_b32 = Crypt::decrypt($row['totp_secret_encrypted'], base64_decode($_SESSION['otp_pwd_hash']), base64_decode($row['totp_iv']));
            //$tokenSecret = Base32::encode($tokenSecret_b32);

            $description = Crypt::decrypt($row['totp_description'], base64_decode($_SESSION['otp_pwd_hash']), base64_decode($row['totp_iv']));

            $icon = Crypt::decrypt($row['totp_icon'], base64_decode($_SESSION['otp_pwd_hash']), base64_decode($row['totp_iv']));

            if($icon == '' || !Otp::iconExists($icons, $icon)) {
                $icon = 'images/key.jpg';
            } else {
                $icon = Image::ICON_DIRECTORY . $icon;
            }

            //if decrypt failed, we need no array
            if(!is_null($description)) {
                array_push($totpValues, array(
                    'totp_id' => $row['totp_id'],
                    'totp_description' => $description,
                    'totp_icon' => $icon,
                    'totp_iv_b64' => $row['totp_iv'],
                    'totp_ts_hr' => date("Y-m-d H:i", strtotime($row['totp_ts'])),
                    'token' => (new Totp())->GenerateToken($tokenSecret_b32),
                    'totp_description_b64' => $row['totp_description'],
                    'totp_icon_b64' => $row['totp_icon'],
                    'totp_ts' => $row['totp_ts'],
                    'totp_secret_b64' => $row['totp_secret_encrypted']
                ));
            }
        }

        return Otp::otpArraySort($totpValues, 'totp_description', 'SORT_ASC');

    }

    /**
     * Deliver single token
     *
     * @param int $id Id of totp entry
     *
     * @return string The token, '' in case of error
     */
    public static function getSingleToken(int $id): string
    {
        global $db;
        $sql = "select totp_secret_encrypted, totp_iv from totp where totp_id=?";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(1, $id);

        $token = '';
        if (!$stmt->execute()) {
            print_r($stmt->errorInfo());
        } else {

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                $tokenSecret_b32 = Crypt::decrypt($row['totp_secret_encrypted'], base64_decode($_SESSION['otp_pwd_hash']), base64_decode($row['totp_iv']));

                //if decrypt failed, do not deliver any token
                if(is_null($tokenSecret_b32)) {
                    return '';
                }

                $token = (new Totp())->GenerateToken($tokenSecret_b32);

            }
        }
        return $token;
    }


    /**
     * Update description to a totp entry
     *
     * @param string $description_crypted_base64 Thedescription, encrypted, Base64 encoded
     * @param int $totp_id The ID of the totp entry
     *
     * @return bool True if success, otherwise failed
     */
    public static function updateDescription(string $description_crypted_base64, int $totp_id): bool
    {
        global $db;
        $sql = "update totp set totp_description=? where totp_id=?";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(1, $description_crypted_base64);
        $stmt->bindValue(2, $totp_id);
        if (!$stmt->execute()) {
            print_r($stmt->errorInfo());
            return false;
        } else {
            return true;
        }

    }

    /**
     * Update Icon to a totp entry
     *
     * @param string $icon The icon, encrypted, Base64 encoded
     * @param int $totp_id The ID of the totp entry
     *
     * @return bool True if success, otherwise failed
     */
    public static function updateIcon(string $icon, int $totp_id): bool
    {
        global $db;
        $sql = "update totp set totp_icon=? where totp_id=?";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(1, $icon);
        $stmt->bindValue(2, $totp_id);
        if (!$stmt->execute()) {
            print_r($stmt->errorInfo());
            return false;
        } else {
            return true;
        }

    }

    /**
     * Check if database is at most recent version, if not migrate
     *
     */
    public static function migrateDatabase():bool {
        global $user;
        $dbversion = Db::getLatestDBVersion();

        $loopCount = 0;

        while($dbversion < Version::DBVERSION_EXPECTED && $loopCount < 10) {
            $loopCount++;
            $res = Db::setupDBVersion($user, $dbversion + 1);
            if($res===false) {
                die("Migration of database failed");
            }
        }
        if($dbversion < Version::DBVERSION_EXPECTED) {
            Otp::relocate("index.php?action=logoff?hint=".urlencode("Too many migration steps. Please login again to finish database migration"));
        }
        return true;
    }
    /**
     * Setup for database connectivty and structures
     *
     */
    public static function dbsetup()
    {
        global $db;

        if(!Image::checkIconFolder()) {
            die("Need to create folder ".Image::ICON_DIRECTORY." and ".Image::ICON_DIRECTORY."raw/, but could not");
        }

        if(!Image::checkUploadFolder()) {
            die("Need to create folder ".Image::UPLOAD_DIRECTORY.", but could not");
        }

        if (file_exists(__DIR__ . '/../database.php')) {
            die("DB set already, abort! In case you want to create a new database setup, remove " . __DIR__ . "/database.php first");
        }

        $dbserver = $_POST['dbserver'];
        $port = $_POST['dbport'];
        $dbname = $_POST['dbname'];
        $user = $_POST['dbuser'];
        $pass = $_POST['dbpass'];

        if (strlen($dbserver) > 0 && strlen($port) > 0 && strlen($dbname) > 0 && strlen($user) > 0 && strlen($pass) > 0) {

            try {
                $db = new PDO("pgsql:host=" . $dbserver . ";port=" . $port . ";dbname=" . $dbname, $user, $pass);
                //$db->exec("set names utf8");


                $fileres = Db::writeDBSetup($dbserver, $port, $dbname, $user, $pass);

                if($fileres===false) {
                    die("<br>We found a previous database and database entries, abort!");
                }


                $DBVersion = Db::getLatestDBVersion();
                if ($DBVersion < 1) {
                    $res = Db::setupDBVersion($user, 1);
                    if($res === false) {
                        try {
                            unlink(__DIR__ . '/../database.php');
                        } catch (Exception $ex) {

                        }
                        die("<br>Creating database scheme failed, abort!");
                    }
                } else {
                    try {
                        unlink(__DIR__ . '/../database.php');
                    } catch (Exception $ex) {

                    }
                    die("<br>We found a previous database and database entries, abort!");
                }
                session_abort();
                session_destroy();

                header("Location: index.php");
                exit;

            } catch (PDOException $exception) {
                echo "Connection error: " . $exception->getMessage();
                die("<br>Connection failed, please check if database is reachable, and database name and user exist");
            }

        } else {
            die("Missing data, please try again");
        }
    }

    /**
     * Creates database.php file
     *
     * @param string $dbserver Hostaddress of database server
     * @param string $port Port of database server
     * @param string $dbname Databse name
     * @param string $user Username
     * @param string $pass Password
     *
     * @return bool True on success
     */
    public static function writeDBSetup(string $dbserver, string $port, string $dbname, string $user, string $pass): bool
    {
        try {
            $string = '<?php
    
            $dbserver=' . "'" . $dbserver . "'" . ';
            $port=' . "'" . $port . "'" . ';
            $dbname=' . "'" . $dbname . "'" . ';
            $user=' . "'" . $user . "'" . ';
            $pass=' . "'" . $pass . "'" . ';
            
            $db = new PDO("pgsql:host=" . $dbserver . ";port=" . $port . ";dbname=" . $dbname, $user, $pass);

            //$db->exec("set names ' . "'" . 'utf8' . "'" . '");
            
            ';
            $file = __DIR__ . '/../database.php';

            file_put_contents($file, $string);

            return true;
        } catch (Exception $ex) {
            echo $ex->getMessage();
            return false;
        }

    }

    /**
     * Query database structure version
     *
     * @return int version number of database structure
     */
    public static function getLatestDBVersion(): int
    {
        try {
            global $db;
            $sql = "select max(dbversion_number) as dbversion_number from dbversion";
            $stmt = $db->prepare($sql);

            if (!$stmt->execute()) print_r($stmt->errorInfo());
            $dbversion_number = 0;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                $dbversion_number = $row['dbversion_number'];
            }
            return $dbversion_number;
        } catch (PDOException $exception) {
            return -1;
        }
    }

    /**
     * Create database scheme with given version
     *
     * @param string $dbowner The database user owning tables, indexes
     * @param int $dbversion The schema version to apply
     *
     * @return bool True if success
     */
    public static function setupDBVersion(string $dbowner, int $dbversion): bool
    {
        global $db;

        switch ($dbversion) {
            case 1:
                try {

                    $db->exec('CREATE TABLE IF NOT EXISTS public.totp
                    (
                        totp_id serial NOT NULL,
                        totp_description text NOT NULL,
                        totp_icon text NULL,
                        totp_ts timestamp without time zone NOT NULL,
                        totp_secret_encrypted text NOT NULL,
                        totp_iv text NOT NULL,
                        CONSTRAINT pkey_totp_id PRIMARY KEY (totp_id)
                    )
                    WITH (
                        OIDS = FALSE
                    )
                    TABLESPACE pg_default;');
                        $db->exec('ALTER TABLE IF EXISTS public.totp
                        OWNER to ' . $dbowner . ';');
                        $db->exec('CREATE INDEX IF NOT EXISTS idx_totp_totp_id
                        ON public.totp USING btree
                        (totp_id ASC NULLS LAST)
                        TABLESPACE pg_default;');

                        $db->exec('CREATE TABLE IF NOT EXISTS public.dbversion
                    (
                        dbversion_id serial NOT NULL,
                        dbversion_number integer NOT NULL,
                        CONSTRAINT pkey_dbversion_id PRIMARY KEY (dbversion_id)
                    )
                    WITH (
                        OIDS = FALSE
                    )
                    TABLESPACE pg_default;');
                        $db->exec('ALTER TABLE IF EXISTS public.dbversion
                    OWNER to ' . $dbowner . ';');

                    $db->exec('insert into dbversion (dbversion_number) values(1);');
                    return true;
                } catch (Exception $ex) {
                    echo $ex->getMessage();
                }
                break;
            case 2:
                try {


                    $db->exec('update dbversion set dbversion_number=' . $dbversion);
                    return true;
                } catch (Exception $ex) {
                    echo $ex->getMessage();
                }
                break;
            case 999999999: //template
                try {


                    $db->exec('update dbversion set dbversion_number=' . $dbversion);
                    return true;
                } catch (Exception $ex) {
                    echo $ex->getMessage();
                }
                break;
            default:

        }

        return false;

    }

    /**
     * Store TOTP entry
     *
     * @param string $description Description
     * @param string $icon Filename of icon
     * @param string $secret The secret provided by the TOTP consumer
     * @param string $iv The IV used to encrypt this entry
     *
     * @return bool true if success
     */
    public static function dbStoreTOTPEntry(string $description, string $icon, string $secret, string $iv): bool
    {
        global $db;
        $sql = "insert into totp (totp_description, totp_icon, totp_ts, totp_secret_encrypted, totp_iv) 
            values(?,?,now(),?,?)";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(1, $description);
        $stmt->bindValue(2, $icon);
        $stmt->bindValue(3, $secret);
        $stmt->bindValue(4, $iv);
        if (!$stmt->execute()) {
            print_r($stmt->errorInfo());
            return false;
        }
        return true;
    }

    /**
     * Store TOTP entry, but enrypted
     *
     * @param string $description_b64 Description
     * @param string $icon_b64 Filename of icon
     * @param string $secret_b64 The secret provided by the TOTP consumer
     * @param string $iv_b64 The IV used to encrypt this entry
     * @param string $ts The timestamp
     *
     * @return bool true if success
     */
    public static function dbStoreTOTPEntry_raw(
        string $description_b64, string $icon_b64, string $ts, string $secret_b64, string $iv_b64): bool
    {
        global $db;
        $sql = "insert into totp (totp_description, totp_icon, totp_ts, totp_secret_encrypted, totp_iv) 
            values(?,?,?,?,?)";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(1, $description_b64);
        $stmt->bindValue(2, $icon_b64);
        $stmt->bindValue(3, $ts);
        $stmt->bindValue(4, $secret_b64);
        $stmt->bindValue(5, $iv_b64);
        if (!$stmt->execute()) {
            print_r($stmt->errorInfo());
            exit;
        }
        return true;
    }

}
