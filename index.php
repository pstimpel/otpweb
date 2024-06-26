<?php
include_once(__DIR__ . '/inc/init.php');

//Standard template
$template = 'welcome.tpl';
$pwdfocus = false;
$action = '';

if (isset($_GET['action'])) {
    $action = $_GET['action'];
}
$passwordrelation = '';
$loggedIn = 0;
$loggedInSmarty = 0;

$targetDBSetup = false;
if (isset($database_set)) {
    if ($database_set === false) {
        $template = 'dbsetup.tpl';

        $targetDBSetup = true;
    }
}

if (isset($_SESSION['otp_loginvaliduntil']) && is_numeric($_SESSION['otp_loginvaliduntil']) && !$targetDBSetup) {
    if (Session::otpnow() < $_SESSION['otp_loginvaliduntil']) {
        $loggedIn = true;
        $loggedInSmarty = 1;
        if ($action != "getclock") {
            $_SESSION['otp_loginvaliduntil'] = Session::otpnow() + Session::SESSION_LIFETIME_SECONDS;
        }
    }
}

$runHIBPcheck = 0;
if(isset($_SESSION['otp_checkhash']) && strlen($_SESSION['otp_checkhash'])>0) {
    $runHIBPcheck = 1;
}

$notifymessage = '';
if ($loggedIn === true) {
    if(isset($_GET['hint']) && strlen($_GET['hint'])) {
        $notifymessage=str_replace("'", "\'",$_GET['hint']);
    }
    switch ($action) {
        case "restoreupload":
            Otp::restoreupload();
            exit;
        case "restore":
            $template = 'restore.tpl';
            break;
        case "backup":
            Otp::backup();
            exit;
        case "logoff":
            Session::logoff();
            exit;
        case "new":
            $template = "newentry.tpl";
            Otp::prepareNewEntry();
            break;
        case "storeentry":
            Otp::newEntry();
            exit;
        case "delete":
            Otp::delEntry($_GET['id']);
            exit;
        case "gettokenbyid":
            echo Otp::getTokenById($_GET['id']);
            exit;
        case "getclock":
            echo Otp::getClock();
            exit;
        case "refreshsession":
            echo "200";
            exit;
        case "showicon":
            $template = 'showicon.tpl';
            Otp::showIcon();
            break;
        case "deleteicon":
            Otp::deleteIcon();
            break;
        case "showicons":
            $template = 'showicons.tpl';
            Otp::showIcons();
            break;
        case "picupload":
            $template = 'showicons.tpl';
            Otp::picUpload();
            break;
        case "storedescription":
            echo Otp::storedescription();
            exit;
        case "geticonwindowhtml":
            echo Otp::getIconWindowHtml();
            exit;
        case "updateicon":
            echo Otp::storeUpdateIcon();
            exit;
        case "checkpassword":
            echo Session::checkPasswordAgainstExternal();
            exit;
        default:
            Otp::showOtpValues();

    }
} else {
    if(!$targetDBSetup) {
        $template = 'login.tpl';
        $pwdfocus = 1;
    }

    switch ($action) {
        case "dbsetup":
            Db::dbsetup();
            exit;
        case "login":
            Session::login();
            exit;
        case "getclock":
            echo Otp::getClock();
            exit;
        case "gettokenbyid":
            echo "Session Timeout";
            exit;
        case "refreshsession":
            echo "Session Timeout";
            exit;
        case "storedescription":
            echo "Session Timeout";
            exit;
        case "updateicon":
            echo "Session Timeout";
            exit;
        default:
            $passwordrelation=Session::createPasswordRelation();

    }
}




if (!empty($smarty)) {

    $smarty->assign('runHIBPcheck', $runHIBPcheck);

    $smarty->assign('passwordrelation', $passwordrelation);

    $smarty->assign('loggedIn', $loggedInSmarty);

    $smarty->assign('pwdfocus', $pwdfocus);

    $smarty->assign('template', $template);

    $smarty->assign('notifymessage', $notifymessage);

    $smarty->display('index.tpl');

}


