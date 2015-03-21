<?php
class UploadWidget extends Widget
{
    protected $title;
    protected $controller;
    protected $callback;
    /**
     * array of value to be rendered in a hidden form field. The data will be
     * encoded in JSON formatt and stored in a hidden field Upload[data].
     */
    public $extraData = array();
    
    public function __construct($options=array())
    {
        foreach($options as $key => $value)
            $this->$key = $value;
        
        if(!isset($this->controller))
            throw new Exception(__CLASS__.".controller attribute is not defined. ");
    }
    
    public function setCallback($callback)
    {
        $this->callback = $callback;
    }

    public function widget()
    {
        echo '<style>';
        $this->renderFile('upload/upload.css');
        echo '</style>';
        
        echo MYHTML::render(array(
            'tag' => 'div',
            'attr' => array('id'=>$this->getId(), 'class'=>'upload_widget'),
            isset($this->title) ? MYHTML::tag('h1', $this->title): '',
            array(
                'tag' => 'form',
                'attr' => array(
                    'id' => $this->getIdFor('form'),
                    'method'=>'post',
                    //'action' => $this->controller,
                    'enctype'=>'multipart/form-data'
                ),
                MYHTML::hidden('Upload[id]', $this->getId(), array('id'=>'Upload_id')),
                MYHTML::hidden('Upload[data]', MYHTML::escape(json_encode($this->extraData)), array('id'=>'Upload_data')),
                MYHTML::file($this->getId(), array('id'=>$this->getIdFor('file'), 'class'=>'file')),
                MYHTML::submit(tt('Upload'), array('id'=>$this->getIdFor('submit'), 'class'=>'submit')),
            ),
            MYHTML::tag('h4', tt('Loading'.'....'), array('id'=>$this->getIdFor('loading'), 'class'=>'loading')),
            MYHTML::tag('div', '', array('id'=>$this->getIdFor('message'), 'class'=>'message')),
            
        ));
        
        echo '<script>';
        $this->renderFile('upload/upload.js');
        echo '</script>';
        
        $script = "
            var upload = new UploadWidget({
                '$'         : jQuery,
                'id'        : '{$this->getId()}',
                'url'       : '{$this->controller}',
                'callback'  : ". (isset($this->callback) ? $this->callback : 'null') . "
            });
        ";
        echo MYHTML::jquery($script);
    }
}
?>