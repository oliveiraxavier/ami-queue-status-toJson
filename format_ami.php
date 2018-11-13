<?php
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