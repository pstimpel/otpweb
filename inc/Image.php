<?php

class Image
{
    /**
     * Where to upload Icons
     *
     **/
    public const UPLOAD_DIRECTORY = 'uploads/';

    /**
     * Where to read Icons from
     *
     **/
    public const ICON_DIRECTORY = 'icons/';

    /**
     * X and Y dimension of final icon
     *
     **/
    public const ICON_DIMENSION = 64;

    /**
     * Creates a single icon from name and base64 encoded stream
     *
     * @param string $iconname Filename of icon
     * @param string $iconstream_b64 Base64 encoded datastream of icon
     *
     * @return bool True on success, otherwise false
     */
    public static function createIconFromStream(string $iconname, string $iconstream_b64):bool {
        $binarydata = base64_decode($iconstream_b64);

        $file = fopen(Image::ICON_DIRECTORY.$iconname, 'wb');
        if ($file === false) {
            return false;
        }
        fwrite($file, $binarydata);
        fclose($file);
        return true;
    }

    /**
     * Delete all icons
     *
     * @return bool True on success, otherwise false
     */
    public static function deleteAllIcons():bool {
        $files = glob(self::ICON_DIRECTORY.'*');
        foreach($files as $file) {
            if(is_file($file)) {
                unlink($file);
            }
        }
        return true;
    }



    /**
     * Checks if the Upload folder exists
     *
     * @return bool True on success, otherwise false
     */
    public static function checkUploadFolder():bool {
        return self::checkIfFolderExists(self::UPLOAD_DIRECTORY);
    }

    /**
     * Checks if the Icon folders exists
     *
     * @return bool True on success, otherwise false
     */
    public static function checkIconFolder():bool {
        $r1 = self::checkIfFolderExists(self::ICON_DIRECTORY);
        $r2 = self::checkIfFolderExists(self::ICON_DIRECTORY."raw/");
        if($r1 && $r2) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Checks if a folder exists, creates it if missing
     *
     * @param string $folder The folder to check
     *
     * @return bool True on success, false if not existing and not possible to create
     */
    public static function checkIfFolderExists(string $folder):bool {
        if(!file_exists($folder)) {
            try {
                mkdir($folder, 0777);
            } catch (Exception $e) {
                echo $e->getMessage();
                return false;
            }
        }
        if(!is_writable($folder)) {
            return false;
        }
        return true;
    }

    /**
     * Picture upload
     *
     * receives jpg, png or gif, less than 20 MB, square, at least
     * ICON_DIMENSION of size. Resizes file if greater than ICON_DIMENSION px.
     * File will end up in ICON_DIRECTORY
     *
     * @return string Is OK on success, otherwise contains the error message
     */
    public static function picUpload():string
    {
        if(!self::checkUploadFolder()) {
            return "Upload folder not existing";
        }
        if(!self::checkIconFolder()) {
            return "Icon folders not existing";
        }

        $globalhint = "";
        if ((
            ($_FILES["file"]["type"] == "image/gif") || ($_FILES["file"]["type"] == "image/jpeg") || ($_FILES["file"]["type"] == "image/png")
            || ($_FILES["file"]["type"] == "image/x-png") || ($_FILES["file"]["type"] == "image/pjpeg")) && ($_FILES["file"]["size"] < 20000000)) {
            if ($_FILES["file"]["error"] > 0) {
                $globalhint = "Wrong filetype or filesize: " . $_FILES["file"]["error"];
            } else {
                //echo "Upload: " . $_FILES["file"]["name"] . "<br />";
                //echo "Type: " . $_FILES["file"]["type"] . "<br />";
                //echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
                //echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />";

                if (file_exists(Image::UPLOAD_DIRECTORY  . $_FILES["file"]["name"])) {
                    $globalhint = $_FILES["file"]["name"] . " already exists in ".Image::UPLOAD_DIRECTORY;
                } else {
                    //create a unique filename
                    //get end after last dot
                    $file = explode(".", $_FILES["file"]["name"]);
                    $fileendung = $file[sizeof($file) - 1];
                    //print_r($file);
                    //myfile.jpg should turn into myfile_890af9.jpg
                    $myfilename = strtolower( $file[0] . "_" . substr(md5(date("Y-m-d H:i:s") . rand(1, 1000)),0,6) . '.' . $fileendung );
                    move_uploaded_file($_FILES["file"]["tmp_name"], Image::ICON_DIRECTORY."raw/" . $myfilename);

                    $image = new SimpleImage();
                    $image->load(Image::ICON_DIRECTORY."raw/" . $myfilename);

                    if ($image->getHeight() < Image::ICON_DIMENSION || $image->getWidth() < Image::ICON_DIMENSION || $image->getHeight() != $image->getWidth()) {
                        $globalhint = "File Dimensions don't fit, please resize your image to a minimum of ".Image::ICON_DIMENSION." x ".Image::ICON_DIMENSION." px., and make it square";

                        unlink(Image::ICON_DIRECTORY."raw/" . $myfilename);
                    } else {
                        if($image->getHeight() > Image::ICON_DIMENSION) {
                            $image->resizeToHeight(Image::ICON_DIMENSION);
                            $image->resizeToWidth(Image::ICON_DIMENSION);
                        }
                        $image->save(Image::ICON_DIRECTORY . $myfilename);
                        unlink(Image::ICON_DIRECTORY."raw/" . $myfilename);
                        return "OK";
                    }
                }
            }
        } else {
            return "Invalid file";
        }
        return $globalhint;
    }

}