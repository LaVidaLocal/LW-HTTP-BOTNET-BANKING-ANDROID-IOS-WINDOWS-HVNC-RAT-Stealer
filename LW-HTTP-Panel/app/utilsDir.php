<?php

class utilsDir
{
    function frontendTables()
    {
        require_once 'utils.php';
        $utils = new utils();
        $arrayFiles = $utils->getFiles($_SESSION['path_build']);
        $table = "";
        foreach ($arrayFiles as $path) {
            $nameFile = basename($path);
            $table = $table . "<tr>
                                <td>$nameFile</td>
                                <td><a href='getfile.php?download=" . base64_encode($nameFile) . "'>Download</a></td>
                             </tr>";
        }

        return $table;
    }

    function file_download($file)
    {
        if (file_exists($file)) {
            if (ob_get_level()) {
                ob_end_clean();
            }

            if (false !== (fopen($file, 'r'))) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename=' . basename($file));
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file)); //Remove

                echo file_get_contents($file);
            }
            exit;
        }
    }
}
