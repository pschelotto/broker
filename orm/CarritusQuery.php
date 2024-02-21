<?php

namespace orm;

class CarritusQuery
{
	public function __construct($className, $alias=null)
	{
		$this->className = $className;
		$this->alias = $alias;

		$this->select = "*";
		$this->from = $className ? ("FROM ".$className::getTableName().' '.$alias) : '';
		$this->join = array();
		$this->left_join = array();
		$this->inner_join = array();
		$this->right_join = array();
		$this->where = 'true';
		$this->and_where = array();
		$this->or_where = array();
		$this->group_by= '';
		$this->order_by= '';
		$this->limit = '';
		$this->start = '';
	}
	public function select($str){
		$this->select = $str;
		return $this;
	}
	public function from($str){
		$this->from = $str;
		return $this;
	}
	public function join($str){
		$this->join[] = $str;
		return $this;
	}
	public function leftJoin($str){
		$this->left_join[] = $str;
		return $this;
	}
	public function rightJoin($str){
		$this->right_join[] = $str;
		return $this;
	}
	public function innerJoin($str){
		$this->inner_join[] = $str;
		return $this;
	}
	public function where($str){
		$this->where = $str;
		return $this;
	}
	public function andWhere($str){
		$this->and_where[] = $str;
		return $this;
	}
	public function orWhere($str){
		$this->or_where[] = $str;
		return $this;
	}
	public function limit($str){
		$this->limit = $str;
		return $this;
	}
	public function start($str){
		$this->start = $str;
		return $this;
	}
	public function groupBy($str){
		$this->group_by = $str;
		return $this;
	}
	public function orderBy($str){
		$this->order = $str;
		return $this;
	}
	public function getSql()
	{
		$this->query_string = "SELECT $this->select";
		$this->query_string .= ' '.$this->from;

		foreach($this->join as $item)
			$this->query_string .= $this->autoJoin($item);
		foreach($this->left_join as $item)
			$this->query_string .= $this->autoJoin($item,'LEFT');
		foreach($this->inner_join as $item)
			$this->query_string .= $this->autoJoin($item,'INNER');
		foreach($this->right_join as $item)
			$this->query_string .= $this->autoJoin($item,'RIGHT');

		$this->query_string .= ' WHERE '.$this->where;
		foreach($this->and_where as $item)
			$this->query_string .= ' AND '.$item;
		foreach($this->or_where as $item)
			$this->query_string .= ' OR '.$item;
			
		if($this->group_by)
			$this->query_string .= ' GROUP BY '.$this->group_by;
		if($this->order_by)
			$this->query_string .= ' ORDER BY  '.$this->order_by;
		if($this->limit)
			$this->query_string .= ' LIMIT '.$this->limit;
		if($this->start)
			$this->query_string .= ' OFFSET '.$this->start;

		return $this->query_string;
	}
	
	public function autoJoin($join,$type='')
	{
		if(preg_match('/(.*?)\.(.*?) (.*)/',$join,$match))
		{
			list($nil,$alias_src,$className,$alias_dst) = $match;
			$table = carritusORM::tableize($className);
			$str = "$type JOIN $table $alias_dst on $alias_dst.id = $alias_src.{$table}_id";
		}
		else
			$str = "$type JOIN $join";

		return " ".trim($str);
	}

	public function execute()
	{
		$query = $this->getSql();
		$cn = $this->className;
		return $cn::query($query);
	}

	public function executeMap()
	{
		$ret = $this->execute();

		$coll = array();
		foreach($ret as $item)
			$coll[$item['id']] = new $this->className($item, false);

		return $coll;
	}
}