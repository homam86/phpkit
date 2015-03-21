<?php

class Widget
{
    protected static $__N;
    protected $__id;
    
    public function __construct($options=array())
    {
        foreach($options as $key => $value)
            $this->$key = $value;
    }
    
    public function getId()
    {
        if(!isset($this->__id))
            $this->__id = get_class($this)."_".(++self::$__N);//time();
        return $this->__id;
    }
    
    public function setId($id) { $this->__id = $id; }
    
    public function getIdFor($name) {  return $this->getId().'_'.$name; }
    
    /**
     * DO NOT OVERRIDE THIS FUCTION UNTIL YOU KNOW WHAT YOU DO. Use begin(), end()
     * and widget() insted.
     */
    public function html()
    {
        ob_start();
        ob_implicit_flush(false);
        
        $this->begin();
        $this->widget();
        $this->end();
        
        return ob_get_clean();
    }
    
    /**
     * Use 'echo' to render what you want.
     */
    public function begin() {}
    /**
     * Use 'echo' to render what you want.
     */
    public function end() {}
    /**
     * Use 'echo' to render what you want.
     */
    public function widget(){}
    
    /**
     * Evaluates a PHP expression. A PHP expression can be any PHP code that has
     * a value.
     * 
     * @param mixed $_expression_ a PHP expression or PHP callback to be evaluated.
     * @param array $_data_ additional parameters to be passed to the above expression
     */
    protected function evaluateExpression($_expression_,$_data_=array())
    {
        extract($_data_);
        return eval('return '.$_expression_.';');
    }
    
    /**
	 * Renders a view file.
	 * This method includes the view file as a PHP script
	 * and captures the display result if required.
	 * @param string $_viewFile_ view file
	 * @param array $_data_ data to be extracted and made available to the view file
	 * @param boolean $_return_ whether the rendering result should be returned as a string
	 * @return string the rendering result. Null if the rendering result is not required.
	 */
    public function renderFile($_viewFile_,$_data_=null,$_return_=false)
    {
        // we use special variable names here to avoid conflict when extracting data
        if(is_array($_data_))
            extract($_data_,EXTR_PREFIX_SAME,'data');
        else
            $data=$_data_;
            
        if($_return_)
        {
            ob_start();
            ob_implicit_flush(false);
            require($_viewFile_);
            return ob_get_clean();
        }
        else
            require($_viewFile_);
    }
}