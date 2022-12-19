<?php

class Session
{

    const SESSION_LIFETIME_SECONDS = 300;

    /**
     * Fills session with password and validity timestamp from $_POST
     *
     *
     *
     */
    public static function login()
    {
        $pass = $_POST['pwd'];
        $_SESSION['otp_pwd_hash'] = base64_encode(hash("sha256", $pass, true));
        $_SESSION['otp_loginvaliduntil'] = Session::otpnow() + Session::SESSION_LIFETIME_SECONDS;
        $_SESSION['otp_checkhash'] = sha1($_POST['pwd']);
        Otp::relocate("index.php");
    }

    /**
     * Returns the timestamp of now
     *
     * @return int timestamp of now
     *
     */
    public static function otpnow(): int
    {
        return strtotime(date("Y-m-d H:i:s"));
    }

    /**
     * Voids session vars, and reloads whole page to allow for new login
     *
     */
    public static function logoff()
    {
        $_SESSION['otp_pwd_hash'] = '';
        $_SESSION['otp_loginvaliduntil'] = 0;

        Otp::relocate("index.php");
    }

    /**
     * calculates left time in Session
     *
     * @return int seconds left, -1 if ran out already
     */
    public static function calculateClock(): int
    {
        $returnvalue = -1;
        if (isset($_SESSION['otp_loginvaliduntil']) && is_numeric($_SESSION['otp_loginvaliduntil'])) {
            if (Session::otpnow() < $_SESSION['otp_loginvaliduntil']) {

                $returnvalue = ($_SESSION['otp_loginvaliduntil'] - Session::otpnow());

            }
        }
        return $returnvalue;
    }

    /**
     * Queries HIBP for password security (k-anon)
     *
     * @return string resultcode 200=ok, 500=failed, 600=skipped, 0=password found
     */
    public static function checkPasswordAgainstExternal():string {
        if(isset($_SESSION['otp_checkhash']) && strlen($_SESSION['otp_checkhash'])>5) {
            $fullhash = $_SESSION['otp_checkhash'];
            $_SESSION['otp_checkhash']='';
            $_SESSION['pwcheckrun']=0;

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, "https://api.pwnedpasswords.com/range/".substr($fullhash,0,5));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FRESH_CONNECT, true); 		// TRUE to force the use of a new connection instead of a cached one.
            curl_setopt($curl, CURLOPT_FORBID_REUSE, true); 		// TRUE to force the connection to explicitly close when it has finished processing, and not be pooled for reuse.
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);		// TRUE to follow any "Location: " header that the server sends as part of the HTTP header (note this is recursive,

            $http_request_result = curl_exec ($curl);
            $http_return_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if($http_return_code==200) {

                $hashes = explode("\n", $http_request_result);

                for($i=0;$i<sizeof($hashes);$i++) {
                    $hashes[$i]=strtoupper(substr($fullhash,0,5)).$hashes[$i];
                    $thishash = explode(':', $hashes[$i]);

                    if(strtoupper($thishash[0])==strtoupper($fullhash)) {

                        echo "0";
                        exit;
                    }
                }
                echo "200";
                exit;
            }
            echo "500";
            exit;
        } else {
            echo "600";
            exit;
        }
    }

}