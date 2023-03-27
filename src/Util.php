<?php

namespace Ajian\Helper;

class Util
{
    /**
     * http_get请求
     * @param string $url 请求地址
     * @return array
     */
    static function https_get($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//设置以文件流的形式返回
        curl_setopt($curl, CURLOPT_HEADER, 1);        //设置响应头信息是否返回
        $result = curl_exec($curl);
        curl_close($curl);
        list ($header, $body) = explode("\r\n\r\n", $result, 2);
        preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
        $info ['cookie'] = substr($matches [1] [0], 1);
        $info ['content'] = $body;
        return $info;
    }

    /**
     * 将xml转为array格式
     * @param string $xml xml字符串
     * @return array xml数组
     */
    static function xmlToArray($xml)
    {
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }

    /**
     * 将数组转为xml格式
     * @param array $array 数组
     * @return string xml格式字符串
     */
    static function arrayToXml($array)
    {
        $xml = "<xml>";
        foreach ($array as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";

            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * 创建随机字符串
     * @param int $length 字符串长度
     * @return string
     */
    static function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 获取随机数
     * @param int $min 最小值
     * @param int $max 最大值
     * @param int $precision 保留小数
     * @return float
     */
    static function getRandFloat($min, $max, $precision = 2)
    {
        $rand = $min + mt_rand() / mt_getrandmax() * ($max - $min);
        return round($rand, $precision);
    }

    /**
     * 图片转base64格式
     * @param string $img_path 文件地址
     * @param string $coord 裁剪区域大小，示例：60,320,340,340 坐标x60y320宽340高340
     * @return string base64文件流 
     */
    static function imageToBase64($img_path, $coord=null){
        if (!is_file($img_path)) {
            return null;
        }
        $pathinfo = pathinfo($img_path);
        if (!isset($pathinfo['extension'])){
            return null;
        }
        $ext = $pathinfo['extension'];
        switch ($ext) {
            case 'jpg':
                $im = imagecreatefromjpeg($img_path);
                break;
            case 'png':
                $im = imagecreatefrompng($img_path);
                break;
            case 'gif':
                $im = imagecreatefromgif($img_path);
                break;
        }
    //    list($width, $height) = getimagesize($img_path);
        $thumb = imagecreatetruecolor(400, 400);
        ob_start();
        if($coord){
            $coord = $coord ? explode(',', $coord) : [60, 320, 340, 340];
            list($src_x, $src_y, $src_w, $src_h) = $coord;
            imagecopyresampled($thumb, $im, 0, 0, (int)$src_x, (int)$src_y, 400, 400, (int)$src_w, (int)$src_h);
            imagejpeg($thumb);
        }
        else{
            imagejpeg($im);
        }
        $image_data = ob_get_contents();
        ob_end_clean();
        imagedestroy($im);
        imagedestroy($thumb);
    
        $image_data = base64_encode($image_data);
        return "data:image/{$ext};base64,{$image_data}";
    }

    /**
     * 获取ip地址归属地
     * @param string $ip ip地址
     * @return array 
     */
    static function getIPAddr($ip){
        $client = new \GuzzleHttp\Client();
        $response = $client->get("https://ip.taobao.com/outGetIpInfo",[
            'query' => [
                'ip' => $ip,
                'accessKey' => 'alibaba-inc'
            ]
        ]);
        if($response->getStatusCode() == 200){
            $data = json_decode($response->getBody()->getContents(),true);
            return $data;
        }
        return false;
    }
}