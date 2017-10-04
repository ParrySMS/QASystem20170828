
<?php
error_reporting(E_ALL ^ E_NOTICE);
require_once './src/QcloudApi/QcloudApi.php';

$config = array(
    'SecretId'       => 'AKIDkcaO1VDwtfGf3TROdljXAct0viKlvg8Q',
    'SecretKey'      => 'IRT7cw8GDza0HdswoV0KlCTbYKvPipoc',
    'RequestMethod'  => 'POST',
    'DefaultRegion'  => 'gz');

$wenzhi = QcloudApi::load(QcloudApi::MODULE_WENZHI, $config);

$package = array("content"=>"六四事件是一个历史问题","type"=>2);

$a = $wenzhi->TextSensitivity($package);

if ($a === false) {
    $error = $wenzhi->getError();
    echo "Error code:" . $error->getCode() . ".\n";
    echo "message:" . $error->getMessage() . ".\n";
    echo "ext:" . var_export($error->getExt(), true) . ".\n";
} else {
    var_dump($a);
}

echo "\nRequest :" . $wenzhi->getLastRequest();
echo "\nResponse :" . $wenzhi->getLastResponse();
echo "\n";
