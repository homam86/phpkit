<?php

class DbDataSource implements Iterator {
    protected $row;
    protected $index = -1;
    protected $resource;
    
    public function __construct($resource)
    {
        $this->resource = $resource;
    }
    
    /**
     * Returns the number of rows in the result set.
     */
    public function count()
    {
        return mysql_num_rows($this->resource);
    }
    
    function rewind()
    {
        if($this->index<0)
        {
            $this->row = mysql_fetch_assoc($this->resource);
            $this->index = 0;
        }
        else
            throw new Exception('DbDataSource cannot rewind. It is a forward-only.');
    }
    
    /**
      * Returns the index of the current row.
    */
    public function key()
    {
        return $this->index;
    }

	/**
     * Returns the current row.
     */
    public function current()
    {
        return $this->row;
    }

    /**
     * Moves the internal pointer to the next row.
     */
    public function next()
    {
        $this->row = mysql_fetch_assoc($this->resource);
        $this->index++;
		return $this->row;
    }

    /**
     * Returns whether there is a row of data at current position.
     */
    public function valid()
    {
        return $this->row!==false;
    }
    
	public function toArray()
	{
		$array = array();
		while( $row = $this->next() )
		{
			$array[] = $row;
		}
		return $array;
	}
    
}
