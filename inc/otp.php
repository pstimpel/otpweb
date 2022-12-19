<?php

// https://fontawesome.com/v4/cheatsheet/


class Otp
{
    /*
    function checkOOTP(): string {
        # Generate a new secret key
        # Note: GenerateSecret returns a string of random bytes. It must be base32 encoded before displaying to the user. You should store the unencoded string for later use.
        //$secret = Totp::GenerateSecret(16);
        $key='';
        try {
            $secret = Base32::decode(str_replace(' ', '', "YOUR SECRET GOES IN HERE"));

            # Display new key to user so they can enter it in Google Authenticator or Authy
            echo Base32::encode( $secret );

            # Generate the current TOTP key
            # Note: GenerateToken takes a base32 decoded string of bytes.
            $key = (new Totp())->GenerateToken($secret);

        } catch (Exception $e) {
        }

        echo $key;
    }
    */

    /**
     * Max len of description field
     */
    const DESCRIPTION_LENGTH = 100;

    /**
     * Max len of otp secret field
     */
    const TOTP_SECRET_LENGTH = 1024;


    /**
     * Reads binary data of an icon, encodes stream base64
     *
     * @param string $icon Filename of icon
     *
     * @return string Base64 encoded datastream of the icon
     */
    public static function iconBase64(string $icon):string
    {
        //echo Image::ICON_DIRECTORY . $icon;
        if (file_exists(Image::ICON_DIRECTORY . $icon)) {

            $imagedata = file_get_contents(Image::ICON_DIRECTORY . $icon);

            return base64_encode($imagedata);
        } else {
            return "null";
        }
    }

    /**
     * creates backup file and returns it as json with timestamp for download
     *
     */
    public static function backup() {
        $totparray_raw = Db::getOtpValues(0);

        $totparray = array();

        for($i=0;$i<sizeof($totparray_raw);$i++) {
            array_push($totparray, array(
                'totp_iv_b64' => $totparray_raw[$i]['totp_iv_b64'],
                'totp_description_b64' => $totparray_raw[$i]['totp_description_b64'],
                'totp_icon_b64' => $totparray_raw[$i]['totp_icon_b64'],
                'totp_ts' => $totparray_raw[$i]['totp_ts'],
                'totp_secret_b64' => $totparray_raw[$i]['totp_secret_b64']
            ));
        }

        $iconarray_raw=Otp::getIcons();
        $iconarray = array();
        for($i=0;$i<sizeof($iconarray_raw);$i++) {
            array_push($iconarray, array(
                'icon_name' => $iconarray_raw[$i],
                'icon_b64' => self::iconBase64($iconarray_raw[$i])
            ));
        }
        $arr = array(
            'totp' => $totparray,
            'icons' => $iconarray
        );
        $date=date('Y-m-d-H-I-s').'-'.rand(1000,9999);
        header('Content-disposition: attachment; filename=otpweb-'.$date.'.json');
        header('Content-type: application/json');
        echo json_encode($arr);
        exit;
    }

    /**
     * Reads json post, and handles restore
     *
     */
    public static function restoreupload() {
        //load contens from restore file
        $json = file_get_contents($_FILES['file']['tmp_name']);
        $data = json_decode($json, true);

        //checxk, if this is a valid otpweb file
        if(!isset($data['totp'][0]['totp_icon_b64'])) {
            die("No valid OTPWEB backup, it seems");
        }

        //delete all icons
        Image::deleteAllIcons();

        //delete all db data from totp
        Db::deleteAllEntries();

        //recreate Icons
        $failed_icons = false;
        for($i=0;$i<sizeof($data['icons']); $i++) {
            $res = Image::createIconFromStream($data['icons'][$i]['icon_name'], $data['icons'][$i]['icon_b64']);
            if(!$res) {
                $failed_icons = true;
            }
        }


        //recreate database entries
        $failed_totp = false;
        for($i=0;$i<sizeof($data['totp']); $i++) {
            $res = Db::dbStoreTOTPEntry_raw(
                $data['totp'][$i]['totp_description_b64'],
                $data['totp'][$i]['totp_icon_b64'],
                $data['totp'][$i]['totp_ts'],
                $data['totp'][$i]['totp_secret_b64'],
                $data['totp'][$i]['totp_iv_b64']
            );
            if(!$res) {
                $failed_totp = true;
            }
        }

        unlink($_FILES['file']['tmp_name']);

        if($failed_icons || $failed_totp) {
            die("Either recreating Icons or recreating TOTP entries failed, please check the Iconfolder and database");
        } else {
            Otp::relocate('index.php');
        }

    }


    /**
     * Wrapper for updating Icons
     *
     * Gets the icon from $_POST, and calls self:.updateIcon
     *
     * @return string The result of the call
     */
    public static function storeUpdateIcon():string {
        return self::updateIcon($_GET['id'], $_GET['icon']);
    }

    /**
     * Store update Icon information
     *
     * @param int $id the ID of the entry
     * @param string $icon Filename of icon
     *
     * @return string The result of the transaction, 200 = OK
     */
    public static function updateIcon(int $id, string $icon):string {

        $iconsarray=self::getIcons();
        if(!Otp::iconExists($iconsarray, $icon)) {
            return "Icon not existing";
        }

        $result = Db::getOtpValues($id);
        $iv_b64 = $result[0]['totp_iv_b64'];
        $totp_id = $result[0]['totp_id'];
        $returnvalue = "failed to save data";
        if ($id == $totp_id && strlen($icon) > 0) {

            $icon_crypted_b64 = Crypt::encrypt_base64($icon, base64_decode($_SESSION['otp_pwd_hash']), base64_decode($iv_b64));

            if (Db::updateIcon($icon_crypted_b64, $totp_id)) {
                $returnvalue = "200";
            }

        }
        return $returnvalue;

    }

    /**
     * upload new icon
     *
     */
    public static function picUpload() {

        $res = Image::picUpload();
        if($res=='OK') {
            $res="Upload OK";
        }
        self::relocate("index.php?action=showicons&hint=".$res);
    }

    /**
     * Get Array of Icons
     *
     * @return array array of available icons
     */
    public static function getIcons():array
    {
        $icons = array();
        $handler = opendir(Image::ICON_DIRECTORY);
        while ($file = readdir($handler)) {
            if ($file != '.' && $file != '..' && !is_dir($file) && $file != 'raw' ) {
                array_push($icons, $file);
            }
        }
        closedir($handler);
        return self::otpArraySort($icons, 0, 'SORT_ASC');

    }

    /**
     * Delete Icon
     *
     * Checks if Icon exists and deletes it
     * Gets data from $_GET
     *
     */
    public static function deleteIcon() {
        if(file_exists(Image::ICON_DIRECTORY . $_GET['id'])) {
            unlink(Image::ICON_DIRECTORY . $_GET['id']);
        }
        Otp::relocate("index.php?action=showicons");
    }

    /**
     * Checks if icon really exists
     *
     * @param array $iconarray Array of available Icons
     * @param string $icon The Icon to check for
     *
     * @return bool True if Icon really exists in filesystem
     */
    public static function iconExists(array $iconarray, string $icon):bool {
        for($i=0;$i<sizeof($iconarray);$i++) {
            if($iconarray[$i]==$icon) {
                return true;
            }
        }
        return false;
    }

    /**
     * Creates HTML of Icon change window
     *
     *
     * @return string html containning Icons and function calls
     */
    public static function getIconWindowHtml():string {
        $divid=$_GET['divid'];
        $iconsarray=self::getIcons();
        $html="";
        $count=0;
        for($i=0;$i<sizeof($iconsarray);$i++) {
            $count++;
            $html = $html . '<a href="javascript:selectIcon('."'".$divid."'".','."'".Image::ICON_DIRECTORY.$iconsarray[$i]."'".','."'".$iconsarray[$i]."'".')" style="padding:2px;"><img src="'.Image::ICON_DIRECTORY.$iconsarray[$i].'" alt="icon" /></a>';
            if($count==4) {
                $html = $html . '<div style="height:2px;"></div>';
                $count=0;
            }
        }
        return $html;
    }

    /**
     * Shjow single Icon, with choice to delete it
     *
     * Gets the description from $_GET
     *
     */
    public static function showIcon() {
        global $smarty;
        $iconsarray=self::getIcons();
        if(!Otp::iconExists($iconsarray, $_GET['id'])) {
            Otp::relocate("index.php?action=showicons");
        }
        $smarty->assign('icon', $_GET['id']);
        $smarty->assign('icondirectory', Image::ICON_DIRECTORY);

    }

    /**
     * Collects all Icons
     *
     */
    public static function showIcons() {
        global $smarty;
        if(isset($_GET['hint'])) {
            $smarty->assign('uploadresult', $_GET['hint']);
        }
        $smarty->assign('icondirectory', Image::ICON_DIRECTORY);

        $smarty->assign('icons', self::getIcons());

    }

    /**
     * Save description of a certain entry
     *
     * Gets the description from $_POST, encrypts it,
     * and stores it to the database
     *
     * @return string The result of the transaction, 200 = OK
     */
    public static function storedescription(): string
    {
        $result = Db::getOtpValues($_POST['totp_id']);
        $iv_b64 = $result[0]['totp_iv_b64'];
        $totp_id = $result[0]['totp_id'];
        $returnvalue = "failed to save data";
        if ($_POST['totp_id'] == $totp_id && $_POST['totp_iv_b64'] == $iv_b64 && strlen($_POST['totp_description']) > 0) {

            $description_crypted_b64 = Crypt::encrypt_base64(substr($_POST['totp_description'],0,self::DESCRIPTION_LENGTH), base64_decode($_SESSION['otp_pwd_hash']), base64_decode($iv_b64));

            if (Db::updateDescription($description_crypted_b64, $totp_id)) {
                $returnvalue = "200";
            }

        }
        return $returnvalue;
    }

    /**
     * Delivers difference from session timeout compared to now
     *
     */
    public static function getClock()
    {

        $clockleft = Session::calculateClock();
        if ($clockleft == -1) {
            return "Session Timeout";
        } else {
            return $clockleft;
        }

    }

    /**
     * Delivers clear text token for a certain entry specified by $id
     *
     * @param int $id id of the entry
     *
     * @retun string The token or an error message or empty
     *
     */
    public static function getTokenById(int $id): string
    {

        return Db::getSingleToken($id);

    }

    /**
     * Push smarty vars for rendering otp entries
     *
     * Used to fill smarty for vars by getOtpValues
     * Receives optional $id, no idea = all values
     *
     * @param int $id id of the entry, could be 0 for all entries
     *
     */
    public static function showOtpValues(int $id = 0)
    {
        global $smarty;
        $totpSorted = Db::getOtpValues($id);
        $smarty->assign("totpValues", $totpSorted);
    }

    /**
     * Deletes a single entry from database, reloads page
     *
     * @param int $id id of the entry
     *
     */
    public static function delEntry(int $id)
    {

        Db::deleteEntryById($id);
        Otp::relocate("index.php");

    }

    /**
     * Loads page from url, and exits php
     *
     * @param string $url url of page to redirect to
     *
     */
    public static function relocate(string $url)
    {
        header("Location: " . $url);
        exit;
    }

    /**
     * Just some preparation to load newnetry.tpl
     *
     */
    public static function prepareNewEntry() {
        global $smarty;
        $smarty->assign('DESCRIPTION_LENGTH', self::DESCRIPTION_LENGTH);
        $smarty->assign('TOTP_SECRET_LENGTH', self::TOTP_SECRET_LENGTH);

    }

    /**
     * Creates new encrypted token entry in database from $_POST, reloads page
     *
     */
    public static function newEntry()
    {

        $iv = Crypt::createIV();
        $iv_b64 = base64_encode($iv);
        $password = base64_decode($_SESSION['otp_pwd_hash']);

        try {
            $otpsecret = Base32::decode(
                str_replace(' ', '',
                    substr($_POST['secret'],0,self::TOTP_SECRET_LENGTH )
                )
            );
        } catch (Exception $e) {
            die("Base32.decode failed with " . $e->getMessage());
        }
        $otpsecret_crypt_b64 = Crypt::encrypt_base64($otpsecret, $password, $iv);
        $description = Crypt::encrypt_base64(substr($_POST['description'], 0, self::DESCRIPTION_LENGTH), $password, $iv);
        $icon = Crypt::encrypt_base64('', $password, $iv);

        Db::dbStoreTOTPEntry($description, $icon, $otpsecret_crypt_b64, $iv_b64);

        Otp::relocate("index.php");

    }

    /**
     * Sorts array by a certain key
     *
     * @param array $array array to sort
     * @param string $on sort on which key
     * @param string $order sort order, default is descending (SORT_ASC|SORT_DESC)
     *
     * @return array The sorted array
     */
    public static function otpArraySort(array $array, string $on, string $order = 'SORT_DESC'): array
    {
        $new_array = array();
        $sortable_array = array();

        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }

            switch ($order) {
                case 'SORT_ASC':
                    //echo "ASC";
                    asort($sortable_array);
                    break;
                case 'SORT_DESC':
                    //echo "DESC";
                    arsort($sortable_array);
                    break;
            }

            foreach ($sortable_array as $k => $v) {
                $new_array[] = $array[$k];
            }
        }
        return $new_array;
    }
}