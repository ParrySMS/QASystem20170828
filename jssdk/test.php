<?
require "../config/Wxapp";
$appid = APPID;
$appsecret = APPSECRET;
$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appId&secret=$appSecret";
//$res = json_decode($this->httpGet($url));
$res = array();
var_dump($res);


   function httpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        // Ϊ��֤��������������΢�ŷ�����֮�����ݴ���İ�ȫ�ԣ�����΢�Žӿڲ���https��ʽ���ã�����ʹ������2�д����ssl��ȫУ�顣
        // ����ڲ�������д����ڴ˴���֤ʧ�ܣ��뵽 http://curl.haxx.se/ca/cacert.pem �����µ�֤���б��ļ���
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_CAINFO, 'C:/Dev/cacert.pem');
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }

