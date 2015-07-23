<?php
/**
 * Response.php
 * @author Fábio Nogueira
 * @version 0.0.2
 */

class Response {
    /**
     * @param type $data
     * @param type $command Um array no formato: array('id'=>id, data=>data)
     */
    public static function success($data, $command=array(), $encode=true){
        //$CACHE = Cache::getInstance();
        
        $resultType = isset($_REQUEST["wt"]) ? $_REQUEST["wt"] : "json";
        $result = array(
            'status' => 200, 
            'data'   => $data,
            '$command'=> $command
        );
        
        //$CACHE->save(NULL, $result);
        
        switch ( $resultType ){
            case "json":
                $output = $encode ? json_encode($result) : $result;
                header('Content-Type: application/json');
                echo $output;
                break;

            case "php":
                echo "<pre>"; print_r($result);
                break;
        }
        exit;
    }
    public static function error($data, $code=500){
        $resultType = isset($_REQUEST["wt"]) ? $_REQUEST["wt"] : "json";
        
        if ($code===200){
            $code=500;
        }
        
        $result = array(
            "status" => $code, 
            "data"   => $data
        );
        
        switch ( $resultType ){
            case "json":
                echo json_encode($result);
                break;

            case "php":
                echo "<pre>"; print_r($result);
                break;
        }
        exit;
    }
    public static function command($command, $params=NULL) {
        
        //se é uma resposta de um comando, $_REQUEST['$command'] deve ser array('id'=>id, 'data'=>data)
        if ( isset($_REQUEST['$command']) && is_array($_REQUEST['$command']) ) {
            $response_command = $_REQUEST['$command'];
            
            //obtém o id do comando. se não existe sessão, o id é 0(zero)
            if (session_id()==''){
                $command_id = 0;
            }else{
                $command_id = $_SESSION['command_id'];
                
                unset($_SESSION['command_id']);
                unset($_SESSION['command_params']);
            }
            
            //verifica se a resposta é para o comando enviado
            if ( $response_command['id']==$command_id ) {
                return $response_command['data'];
            }
        }
        
        //chegando aqui é poque não existe resposta para o comando
        if (session_id()==''){
            $command_id = 0;
        }else{
            $command_id = uniqid('$cmd_');
            $_SESSION['command_id'] = $command_id;
        }	
	
	Response::success(NULL, array(
            'name'=> $command,
            'id'  => $command_id,
            'data'=> $params
        ));
    }
    public static function confirm($content, $caption){
        return Response::command('confirm', array(
            'caption'=>$caption, 
            'content'=>$content)
        );
    }
}