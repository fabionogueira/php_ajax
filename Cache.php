<?php
class Cache{
    public static function load($url){
        $file = self::urlTopath($url);
        return file_exists($file) ? @file_get_contents($file) : '';
    }
    
    public static function save($url, $content, $time=0){
        $file = self::urlTopath($url);
        if (!file_exists(dirname($file))){
            mkdir(dirname($file),0777,true);
        }
        
        file_put_contents($file, $content);
    }
    
    public static function exist($url){
        $file = self::urlTopath($url);
        return file_exists($file);
    }
    
    public static function wget($url){
        if (Cache::exist($url)){
            return (Cache::load($url));
        }else{
            //GET
            $content = @file_get_contents($url);

            if ($content===false){
                header("HTTP/1.0 404 Not Found");
            }else{
                Cache::save($url, $content);
                return $content;
            }
        }
    }
    
    public static function wpost($url){
        if (Cache::exist($url)){
            echo (Cache::load($url));
        }else{
            //POST
            $headers = ($_POST['headers']) ? $_POST['headers'] : $_GET['headers'];
            $mimeType =($_POST['mimeType']) ? $_POST['mimeType'] : $_GET['mimeType'];

            $session = curl_init($url);

            $postvars = ''; $e='';
            foreach ($_POST as $key=>$value) {

                if (is_array($value)){
                    for ($i=0; $i<count($value); $i++){
                        $postvars .= ($e . $key.'='.$value[$i]);
                        $e = '&';
                    }
                }else{
                    $postvars .= ($e . $key.'='.$value);
                    $e = '&';
                }
            }

            curl_setopt ($session, CURLOPT_POST, true);
            curl_setopt ($session, CURLOPT_POSTFIELDS, $postvars);

            curl_setopt($session, CURLOPT_HEADER, ($headers == "true") ? true : false);

            curl_setopt($session, CURLOPT_FOLLOWLOCATION, true); 
            //curl_setopt($ch, CURLOPT_TIMEOUT, 4); 
            curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

            $content = curl_exec($session);

            if ($mimeType != ""){
                // The web service returns XML. Set the Content-Type appropriately
                header("Content-Type: ".$mimeType);
            }

            curl_close($session);

            if ($content===false){
                header("HTTP/1.0 404 Not Found");
            }else{
                Cache::save($url, $content);
                return $content;
            }
        }
    }
    
    private static function urlTopath($url){
        $postvars = ''; $e='';
        foreach ($_POST as $key=>$value) {
            $postvars .= ($e . $key.'='.$value);
            $e = '&';
        }
        $url .= '?' . $postvars;
        
        $url = str_replace(':', '', $url);
        $url = str_replace('//', '/', $url);
        $p = explode('?', $url);
        if (count($p)>1){
            $a = explode('&', $p[1]);
            sort($a);
            $s = implode('', $a);
            $p[1] = md5(str_replace('=', '', $s));
        }
        
        $p[0] = str_replace('/', '', $p[0]);
        $p[0] = md5(str_replace('.', '_', $p[0]));
        $p = str_split($p[0] . $p[1], 120);
        
        $f = dirname(realpath(__FILE__)) . DIRECTORY_SEPARATOR . 'cache';
        
        for ($i=0; $i<count($p); $i++){
            $f .= DIRECTORY_SEPARATOR . $p[$i];
        }
        
        return $f;
    }
    
    
}
