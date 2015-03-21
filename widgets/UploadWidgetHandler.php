<?php

class UploadWidgetHandler
{
    protected $name;
    protected $path = "uploads/";
    protected $extensions = array("jpeg", "jpg", "png" );
    
    protected $errors;
    
    public function __construct($data=array())
    {
        foreach($data as $key => $value)
            $this->$key = $value;
        
        $this->path = trim($this->path, '/');
        
        if(!isset($this->name))
            throw new Exception(__CLASS__.': You must specifiy the "name" attribute to determione which file to be extracted from $_FILES array.');
        
        $this->errors = array(
            0 => 'UPLOAD_ERR_OK',
            1 => 'UPLOAD_ERR_INI_SIZE',
            2 => 'UPLOAD_ERR_FORM_SIZE',
            3 => 'UPLOAD_ERR_PARTIAL',
            4 => 'UPLOAD_ERR_NO_FILE',
            6 => 'UPLOAD_ERR_NO_TMP_DIR',
            7 => 'UPLOAD_ERR_CANT_WRITE',
            8 => 'UPLOAD_ERR_EXTENSION',
        );
    }
    
    protected function getFileError($code)
    {
        return isset($this->errors[$code]) ? $this->errors[$code] : 'UNKNOW UPLOAD ERR: '.$code;
    }
    
    protected function path($filename)
    {
        return $this->path.'/'.$filename;
    }
    
    protected function url($filename)
    {
        return 'http://'.$_SERVER['HTTP_HOST'].'/'.$this->path($filename);
    }
    
    public function process()
    {
        $response = array('status'=>'OK');
        
        $file = isset($_FILES[$this->name]) ? $_FILES[$this->name] : null;
        if(!isset($file))
            throw new Exception(__CLASS__.': Invalid "name" attribute value.');

        $temporary  = explode(".", $file["name"]);
        $file_ext   = end($temporary);
        
        if(in_array($file_ext, $this->extensions))
        {
            if ($file["error"] > 0)
            {
                $response['status'] = 'ERR';
                $response['message'] = $this->getFileError($_FILES["file"]["error"]);
            }
            else if (file_exists($this->path($file["name"])))
            {
                $response['status'] = 'ERR';
                $response['message'] = tt("The files is already exists");
            }
            else
            {
                $sourcePath = $file['tmp_name']; 
                $targetPath = $this->path($file["name"]);
                move_uploaded_file($sourcePath, $targetPath);
                
                $response['status'] = 'OK';
                $response['fileName'] = $file['name'];
                $response['type'] = $file['type'];
                $response['size'] = $file['size'];
                $response['url'] = $this->url($file['name']);
            }
            
        }
        else {
            $response['status'] = 'ERR';
            $response['message'] = 'INVALID EXTENSION';
        }
        
        echo json_encode($response);
        exit;
    }
}