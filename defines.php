<?php
ini_set('display_errors', 1);

define('IN_SUPPORTED_LANGS', '{"tr_TR":"Turkey", "en_US":"English", "ru_RU":"Russian", "ar_AR":"Arabic"}');


function __trace($exit=false, $message=null)
{
    echo "<hr/><pre>";
    debug_print_backtrace();
    echo "</pre>";
    
    if($exit) {
        if($message) echo "<h5 style='color:#775566'>$message</h5>";
        exit;
    }
}

function __dump($object, $exit=false)
{
    $bt = debug_backtrace();
    $caller = array_shift($bt);
    echo "<hr/><pre>";
    echo "<h3>{$caller['file']} @ L:{$caller['line']}</h3>";
    print_r($object);
    echo "</pre>";
    
    if($exit)
        exit;
}

function __die($object=null)
{
    $bt = debug_backtrace();
    $caller = array_shift($bt);
    echo "<hr/>";
    echo "<h3>{$caller['file']} @ L:{$caller['line']}</h3>";
    if($object)
    {
        echo "<pre>$object</pre>";
    }
    
    exit;
}

function __sqlerr($statement)
{
    if(!ini_get('display_errors'))
        return;
    
    echo "<hr/>";
    echo "<h4>".mysql_errno()." :  " . mysql_error() . "</h4>";
    echo '<pre style="margin:20px;padding:10px;border:1px solid #7799aa;background-color:#ffdddd">';
    echo $statement;
    echo "</pre>";
}

?>