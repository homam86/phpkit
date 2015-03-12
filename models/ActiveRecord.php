<?php

abstract class ActiveRecord extends STable
{
    const NULL_NOTATION = '##NULL##';
    
    public static $_table;
    protected static $_DBOBJ_;

    protected $_isNew = true;
    protected $_relations = array();
    
    
    public function isNew() { return $this->_isNew; }
    
    public function __construct($data = array())
    {
        $this->_isNew = true;

        foreach ($data as $key => $value)
            $this->$key = $value;
    }
    
    public function table() {
        return static::$_table;
    }
    
    public function isAutoIncrement() {
        return false;
    }
    
    /**
     * Return the foreign key name for a given model, so the foreign keys are
     * keyed with the name of model. Actually this array has ambigous meaning.
     * For example, the following array says that the current
     * object has a foreign key 'some_id' to the User model OR the 'some_id' key
     * in User model belogns to the current object:
     * array (
     *      'User' => 'some_id'
     * )
     */
    public function foreignkeys()
    {
        return array();
    }
    
    public function relation($forienkey, $modelName, $onToMany=false, $fetchObjects=true)
    {
        $name = $modelName.(isset($forienkey) ? '_'.$forienkey: '');
        
        if(isset($this->_relations[$name]) && $this->_relations[$name] !== null)
            return $this->_relations[$name];
        
        if($this->isNew())
            return null;
        if($onToMany)
        {
            $FKS = $this->foreignkeys();
            if(!isset($FKS[$modelName]))
                throw new Exception(get_class($this).' does not define a foreign key to '.$modelName);
            $object = $modelName::model()->findAll($FKS[$modelName]." = ".$forienkey, $fetchObjects);
        }
        else
            $object = $modelName::model()->find($forienkey);
            
        if($object)
            $this->_relations[$name] = $object;
            
        return $object;
    }
    
    public function resetRelation($forienkey, $modelName)
    {
        $name = $modelName.(isset($forienkey) ? '_'.$forienkey: '');
        $this->_relations[$name] = null;
            
    }
    
    /**
     * Return the product-type-id
     */
    abstract public function getProductType();
    /**
     * Return an array of the attributes that are mapped to the DB table.
     * The array keys are the class's attributes while the values are the columns'
     * name. Here an example:
     * array(
     *     'id' => 'id',
     *     'firstName' => 'first_name',
     *     'email' => 'mail'
     * )
     */
    abstract public function attributes();
    
    abstract public function toArray();
    
    public function getModelName()
    {
        return get_class($this);
    }
    
    public function getModelClass()
    {
        return get_class($this);
    }
    
    public function key($newval=null)
    {
        $k = $this->keyName();
        if(isset($newval))
            $this->$k = $newval;
            
        return $this->$k;
    }
    
    public function keyName()
    {
        return static::$_key;
            
    }
    
    function isValid()
    {
        if($this->isNew())
            return true;
        
        $camp = Campaign::load($this->id);
        return $camp!=false;
    }
    
    public function attributeLabels()
	{
		return array();
	}
	
	/**
	 * @notice: from Yii framework
	 */
	public function getAttributeLabel($attribute)
	{
		$labels=$this->attributeLabels();
		if(isset($labels[$attribute]))
			return $labels[$attribute];
		else
			return $this->generateAttributeLabel($attribute);
	}
	
	/**
	 * Generates a user friendly attribute label. This is done by replacing underscores or
	 * dashes with blanks and changing the first letter of each word to upper case.
	 * For example, 'department_name' or 'DepartmentName' becomes 'Department Name'.
	 * @notice: from Yii framework
	 */
	public function generateAttributeLabel($name)
	{
		return ucwords(trim(strtolower(str_replace(array('-','_','.'),' ',preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $name)))));
	}
    
    /**
     * This function is used to gather campaign data from product-table(s). By
     * default,  the function calls the static load() function to get campaign
     * data from the product table, but if the product defines extra info in other
     * table YOU MUST re-implement this function to gather the data an comnine
     * them in a single object.
     *
     * This function is used by the static function getBean() to combine all
     * campaign data in a single BEAN (id, campaing, product).
     *
     * @return mixed string, object, array...
     */
    public function cmobine()
    {
        return $this;
    }
    
    /**
     * Get all data from Campaign table and profuct-table(s) and package all data in one object.
     * @param $id the campaign id to be loaded.
     * @return stdClass object with the following structure:
     *     - id: the campaign id
     *     - campaign: the data loaded from Campaign table
     *     - product: the data loaded from product-table(s).
     * the function return FALSE when the id is not valid.
     */
    public function getBean($campaign_id=null)
    {
        $obj = $this;
        if(!isset($campaign_id))
            $campaign_id = $this->key();
        
        if(!isset($campaign_id))
            return NULL; // no campaign
        
        $camp = Campaign::load($campaign_id);
        if(!isset($camp))
            return NULL;

        $class = get_class($this);
        $empty_product = $class::model();
        $product = $empty_product->find($campaign_id);
        
        $bean = new stdClass();
        $bean->id = $campaign_id;
        $bean->campaign = $camp;
        $bean->product = isset($product) ? $product : $empty_product;
        
        return $bean;
    }
    
    public function createBean()
    {
        $class = get_class($this);
        
        $bean = new stdClass();
        $bean->id = null;
        $bean->campaign = new Campaign();
        $bean->product = new $class();
        
        $bean->campaign->productType = $this->getProductType();
        
        return $bean;
    }
    
    /**
     * This function is the same as STable::load() except that it returns NULL
     * if there is no entry with the specified $value.
     */
    public function find($id)
    {

        $table = $this->table();
        $field = $this->keyName();
        $id = mysql_real_escape_string($id);
        $q = "SELECT * FROM {$table} where {$field}='{$id}'";
        $resutl = $this->query(null, $q);
        
        foreach($resutl as $row)
        {
            $class = $this->getModelClass();
            $obj = new $class;
            $obj->bind($row);
            $obj->_isNew = false;
            return $obj;
        }

        return null;
    }
    
    public function findAll($condition, $fetchObjects = true)
    {

        $table = $this->table();
        $field = $this->keyName();
        $q = "SELECT * FROM {$table} " . ($condition ? "WHERE $condition" : '');
        $resutl = $this->query(null, $q);
        
        if($fetchObjects)
        {
            $objects = array();
            $class = $this->getModelClass();
            
            foreach($resutl as $row)
            {
                $obj = new $class;
                $obj->bind($row);
                $obj->_isNew = false;
                $objects[] = $obj;
            }
            
            return $objects;
        }

        return $resutl;
    }
    
    public function save($xss=true)
    {

        $db = Factory::getDBO();
        $key = $this->getKey();


        if($this->_isNew ) {
            $result = $this->insert();
            
            // set the autoincrement id:
            if(($id=mysql_insert_id()))
                $this->key($id);
            //$result = $db->insertObject($this->table(), $this, $key, true, $xss);
        }
        else {
            $result = $this->update();
            //$result = $db->updateObject($this->table(), $this, $key, true, $xss);
        }

        $this->_isNew = $result;
        return $result;
    }    
    
    /**
     * Execute SQL statement.
     */
    public function execute($dbname, $statment)
    {
        if(!isset(self::$_DBOBJ_))
        {
            if(!isset($dbname))
                self::$_DBOBJ_ = Factory::getDBO();
            else {
                self::$_DBOBJ_ = new Database(
                    $config->databaseHost,
                    $config->databaseUser,
                    $config->databasePass,
                    $dbname
                );
            }
        }
        //__dump($statment);
        $result = self::$_DBOBJ_->query($statment);
        if(!$result)
            __sqlerr($statment);
        return $result;
    }
    
    /**
     * Execute query that returns data (SELECT statment).
     * @return DbDataSource
     */
    public function query($dbname, $query)
    {
        $resource = $this->execute($dbname, $query);
        return new DbDataSource($resource);
    }
    
    /**
     * Execute INSERT statement from the current object attributes.
     */
    public function insert()
    {
        $attributes = $this->attributes();
        
        if($this->isAutoIncrement())
            unset($attributes[$this->keyName()]);
        
        $table = $this->table();
        $columns = implode('`, `', array_values($attributes));
        $values = implode("', '", $this->getAttributesValues());
        
        $sql = "INSERT INTO `{$table}` (`$columns`) VALUES('$values')";
        $sql = $this->fixNullNotation($sql);
        
        return $this->execute(null, $sql);
    }
    
    /**
     * Execute INSERT statement from the current object attributes.
     * @param array $attributes if the keys are integers, so the $attributes
     * dontains the name of attributes to be updated. Otherwise, the keys will
     * be the name of attributes and the values represents the new values of the
     * specefied attributes: ex:
     *     array('firstName', 'email') or
     *     array (
     *         'firstName' => 'Jhon',
     *         'email' => 'jhon@local.com'
     *     )
     */
    public function update($attributes=array())
    {
        if($this->isNew())
            throw new Exception('You can not update new object, Try insert insted');
        
        if(!count($attributes))
            $attributes = $this->getAttributesValues();        
        else {
            $temp = array();
            $changes = array();
            $values = $this->getAttributesValues();
            foreach($attributes as $k => $v)
            {
                if(is_numeric($k))
                   $temp[$v] = $values[$v];
                else
                {
                    $changes[$k] = $v;
                    if(!isset($v))
                        $v = self::NULL_NOTATION;
                    $temp[$k] = mysql_real_escape_string($v);
                }
            }
            $attributes = $temp;
        }
        
        if($this->isAutoIncrement())
            unset($attributes[$this->keyName()]);
        
        $table = $this->table();
        $setValues = array();
        foreach($attributes as $attr => $value)
            $setValues[] = "`$attr` = '$value'";
        
        
        $sql = "UPDATE `{$table}` SET " . implode(' , ', $setValues) . " WHERE `{$this->keyName()}` = {$this->key()}";
        $sql = $this->fixNullNotation($sql);
        
        $resutl = $this->execute(null, $sql);
        
        if($resutl && isset($changes))
            foreach($changes as $attr => $val)
                $this->$attr = $val;
        
        return $resutl;
    }
    
    /**
     * Execute INSERT statement with multi values.
     * @param string $table the name of the table to insert the values into it.
     * @param array $attributes the name of attributes. If the DB column is different
     * from the sttribute name, you should put the attribute name as a key and the
     * column name as a value: array('id', 'firstName' => 'first_name')
     * @param array $values contains sub-arrays. We expect that the items in the
     * sub-arrays are keyed with the attributes name;
     * @package boolean $useIndex if true, we will take the values using index,
     * otherwise, we expect the values are keyed with attribues names.
     */
    public function insertBulk($table, $attributes, $values, $useIndex=false)
    {
        $columns = array_values($attributes);
        $temp = array();
        foreach($attributes as $attr => $col)
            if(is_numeric($attr))
                $temp[] = $col; // the column name is the same as attribute
            else
                $temp[] = $attr;
        $attributes = $temp;
        
        $values_string = array();
        foreach($values as $row)
        {
            $i = -1;
            $row_values = array();
            foreach($attributes as $attr)
            {
                $index = $useIndex ? ++$i : $attr;
                if(isset($row[$index]))
                    $v = $row[$index];
                else
                    $v = self::NULL_NOTATION;
                $row_values[] = $v;
            }
            $values_string[] = "'".implode("', '", $row_values)."'";
        }
        
        
        $columns = "`" . implode('`, `', array_values($columns)) . "`";
        $values = "\n (" . implode("),\n (", $values_string) . ")";
        
        $sql = "INSERT INTO `{$table}` ($columns) VALUES $values";
        $sql = $this->fixNullNotation($sql);
        return $this->execute(null, $sql);
    }
    
    protected function getAttributesValues()
    {        
        $values = array();
        foreach($this->attributes() as $attr => $col)
        {
            if(isset($this->$attr))
                $v = $this->$attr;
            else
                $v = self::NULL_NOTATION;
            $values[$attr] = mysql_real_escape_string($v);
        }
        
        if($this->isAutoIncrement())
            unset($values[$this->keyName()]);
            
        return $values;
    }
    
    protected function fixNullNotation($query)
    {
        return strtr($query, array("'".self::NULL_NOTATION."'"=>'NULL'));
    }
}
