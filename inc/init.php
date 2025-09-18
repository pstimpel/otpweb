<?php
date_default_timezone_set('UTC');

ini_set("session.bug_compat_42", "off");
ini_set("upload_max_filesize", "32M");

header('Content-Type:text/html; charset=UTF-8');
header('p3p: CP="NOI ADM DEV PSAi COM NAV OUR OTR STP IND DEM"');

session_start();

$db = '';
$database_set = true;

require_once(__DIR__ . '/version.php');

if (file_exists(__DIR__ . '/../database.php')) {

    require_once(__DIR__ . '/../database.php');

    require_once(__DIR__ . '/db.php');

    global $db;

    include_once(__DIR__ . '/Base32.php');

    include_once(__DIR__ . '/Totp.php');

    require_once(__DIR__ . '/session.php');

    require_once(__DIR__ . '/otp.php');

    require_once(__DIR__ . '/crypt.php');

    require_once(__DIR__ . '/SimpleImage.php');

    require_once(__DIR__ . '/Image.php');

    require_once(__DIR__ . '/tcpdf_barcodes_2d.php');

} else {
    require_once(__DIR__ . '/Image.php');

    if(!Image::checkUploadFolder()) {
        die("Upload folder not existing");
    }
    if(!Image::checkIconFolder()) {
        die("Icon folders not existing");
    }

    require_once(__DIR__ . '/db.php');

    require_once(__DIR__ . '/session.php');

    $database_set = false;

}


//Smarty setup
require_once(__DIR__ . '/../smarty/Smarty.class.php');

$smarty = new Smarty();

$smarty->setTemplateDir(__DIR__ . '/../smarty/templates/');
$smarty->setCompileDir(__DIR__ . '/../smarty/templates_c/');
$smarty->setConfigDir(__DIR__ . '/../smarty/configs/');
$smarty->setCacheDir(__DIR__ . '/../smarty/cache/');

$smarty->assign('OTPVERSION', Version::OTPVERSION);