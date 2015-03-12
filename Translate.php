<?php


class Translate {
    
    /**
     * This function is used to trnslate the $string to the specified $lang.
     * Eventthough there is two parameter, BUT you can just pass a single parameter
     * which is $string, and we will take care the reset. In this case, we will detect
     * the prefered $lang
     */
    public static function t($group, $message=null, $params=array(), $lang=null)
    {
        if(!isset($message)) {
            $message = $group;
            $group = 'default';
        }
        
        
        if(!isset($lang))
            $lang = self::detectLanguage();
        
        return $params!==array() ? strtr($message,$params) : $message;
    }
    
    public static function detectLanguage () {
        return 'en_US';
    }
}

function tt($group, $message=null, $params=array(), $lang=null)
{
    return Translate::t($group, $message, $params, $lang);
}


?>