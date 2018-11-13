<?php
function loginAmi()
{
    $amiIp = '127.0.0.1';
    $socket = fsockopen($amiIp, "5038", $errno, $errstr, 60);
    $login  = "Action: Login".PHP_EOL;
    $login .= "UserName: asterisk".PHP_EOL;
    $login .= "Secret: asterisk".PHP_EOL.PHP_EOL;
    fputs($socket,$login);
    usleep(30000);
    return $socket;
}
    
function logoffAmi($socket)
{
    fputs($socket, "Action: Logoff".PHP_EOL.PHP_EOL);
    fclose($socket);
}

/**
 * Retorna todas as filas em uma
*/
function queueAllByName(){
        
    $content = [
                "Action: QueueStatus".PHP_EOL.PHP_EOL,
            ];

    $socket = loginAmi();
    usleep(20000);
    $authenticateResponse = fread($socket, 4096);
    var_dump($authenticateResponse);
    if(strpos($authenticateResponse, 'Success') !== false){
        foreach ($content as $setDataToSocket) {
            fputs($socket,$setDataToSocket);
        }
        usleep(20000);
        
        stream_set_timeout($socket,1);
        $response = stream_get_contents($socket);            
        logoffAmi($socket);

        $collection = formatArrayAmiToJson($response);
        if(!empty($collection)){
            return $collection;
        }
        return false;
    }

    logoffAmi($socket);
    return false;
}

function formatArrayAmiToJson($string)
{ 
    if(!empty($string)){
        $subst          = '||separator||$0';
        $replaced       = preg_replace('/Event: /', $subst, $string);
        $stringToArray    = explode('||separator||',$replaced);

        $re = '/(^.*?)(:\s)(.*\s?$)/m';
        $subst = '"$1"$2"$3",';
        $jsonDecoded = [];
        if(is_array($stringToArray)){
            if(strpos($string, 'QueueMember')){
                foreach ($stringToArray as $simpleString) {
                    $replaced   = preg_replace($re, $subst,trim($simpleString));
                    $replaced   = preg_replace('/PausedReason:$/m',"\"PausedReason\": \"no_reason\r\",".PHP_EOL,$replaced);
                    $strToJson  = '{'.trim($replaced).'}';
                    $strToJson  = str_replace(',}', '}', $strToJson);
                    $strToJson  = preg_replace('/\s*"/', '"',$strToJson);
                    array_push($jsonDecoded,json_decode($strToJson));
                }
            }else{
                foreach ($stringToArray as $simpleString) {
                    $replaced   = preg_replace($re, $subst,trim($simpleString));
                    $strToJson  = '{'.$replaced.'}';
                    $strToJson  = str_replace(',}', '}', $strToJson);
                    $strToJson  = preg_replace('/\s*"/', '"',$strToJson);
                    array_push($jsonDecoded,json_decode($strToJson));
                }
            }
            if(!empty($jsonDecoded)){
                return $jsonDecoded;
            }
            
        }
    }
    return null;
    
}

//Example usage 
var_dump(queueAllByName());