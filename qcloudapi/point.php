<?php
require_once './src/QcloudApi/QcloudApi.php';
require "../config/qcloud.php";
require "../controller/safe.php";
error_reporting(E_ALL ^ E_NOTICE);

function isNoWarning($content)
{
    //unset($content);
    //$content = isset($_GET["content"]) ? $_GET["content"] : null;
    if (is_null($content)) {
        return null;
    } else {
        $content = safe_check($content);
        $config = array(
            'SecretId' => SECRETID,
            'SecretKey' => SECRETKEY,
            'RequestMethod' => 'POST',
            'DefaultRegion' => 'gz');

        $wenzhi = QcloudApi::load(QcloudApi::MODULE_WENZHI, $config);

        $packageA = array("content" => $content, "type" => 1);
        $packageP = array("content" => $content, "type" => 2);

        $a = $wenzhi->TextSensitivity($packageA);
        $p = $wenzhi->TextSensitivity($packageP);

        if ($a === false) {
            $error = $wenzhi->getError();
            $typeA = 0.49;
            //echo "Error code:" . $error->getCode() . ".\n";
            // echo "message:" . $error->getMessage() . ".\n";
            // echo "ext:" . var_export($error->getExt(), true) . ".\n";
        } else {
            $typeA = $a ["sensitive"];
        }

        if ($p === false) {
            $error = $wenzhi->getError();
            $typeP = 0.49;
            //echo "Error code:" . $error->getCode() . ".\n";
            // echo "message:" . $error->getMessage() . ".\n";
            // echo "ext:" . var_export($error->getExt(), true) . ".\n";
        } else {
            $typeP = $p ["sensitive"];
        }
        $limit = 68;

        if (120 * $typeA > $limit) {
            return 1;
            //色情
        } elseif (110 * $typeP > $limit) {
            return 2;
            //政治敏感
        } else {
            return 0;
        }

    }
}

function inDB($typeA, $typeP, $total, $content)
{

    $database = new medoo(array("database_name" => DATABASE_NAME));
    $insert = $database->insert("qa_test", [
        "text" => $content,
        "typeA" => $typeA,
        "typeP" => $typeP,
        "total" => $total
    ]);
}



