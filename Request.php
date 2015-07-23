<?php

class Request{
    private static $_params= array();    
    private static $_init  = false;

    public static function init($params, $model=NULL){
        if (!self::$_init) {
            self::$_params = self::prepareValue($params, $model);
            self::$_init = true;
        }
    }
    
    /**
     * @example 
        $swap1 = array('true'=>'Y', 'false'=>'N'); //troca true por Y e false por N
        $swap2 = array('Y'=>'true', 'N'=>false);   //troca true por Y e false por N 
        $model = array(
            'change' => array(
                'type'  => 'string', //força o campo 'change' para o tipo string
                '#swap' => $swap1
            ),
            'locked' => array(
                'type'  => 'bool',   //força o campo 'locked' para o tipo boolean
                '#swap' => $swap2
            )
        );
        
        $data = Request::params(NULL, $model);
     */
    
    public static function params($itens=NULL, $model=NULL){
        self::init($_REQUEST, $model);
        
        if (is_null($itens)){
            return self::$_params;
        }else{
            $r = array();
            for ($i=0; $i<count($itens); $i++){
                $k = $itens[$i]; 
                $r[$k] = self::$_params[$k];
            }
            return $r;
        }
    }
    
    public static function param($name){
        self::init($_REQUEST);
        return self::$_params[$name];
    }
    
    public static function prepareValue($value, $model=NULL){
        if (is_string($value)){
            $value = strip_tags(trim($value));
            $value = str_replace('--', '', $value);
            $value = str_replace(';', '', $value);            
        } elseif (is_array($value)){
            foreach ($value as $k=>$v){
                $v = self::prepareValue($v, $model);
                
                if ( is_array($model) ){
                    $definition = $model[$k];
                    if ($definition){//boolean, integer, float, string, array, object, null
                        if ($definition['#swap']){
                            foreach ($definition['#swap'] as $a=>$b){
                                if ($v==$a) $v=$b;
                            }
                        }
                        
                        if ($definition['type']){
                            settype($v, $definition['type']);
                        }
                    }
                }
                
                $value[$k] = $v;
            }
        }
        
        return $value;
    }
}

//$_REQUEST = Request::prepareValue($_REQUEST);
