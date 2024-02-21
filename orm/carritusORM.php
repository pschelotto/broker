<?php

namespace orm;

use App\Lib\Config;
use App\Lib\Exceptions\carritusORMValidateException;
use App\Lib\Exceptions\carritusORMInvalidDataException;
use App\Lib\Exceptions\carritusORMValidateUndefinedColumnException;
use App\Lib\Exceptions\carritusORMValidateExtraDataException;

/**
 * Class carritusORM, this class is created for avoid the use of doctrine. Doctrine 1 only supports one database.
 *
 *
 * This class can act as a:
 * 	-> "Collection" or "Table", in this case the function must be static, for example:
 *		public static function getActivas() {
 *			$q = '';
 *			return self::query($q);
 *		}
 *		And now used like this:
 *		tmpCategoria::getActivas();
 *
 *	-> "Instance" or "Record", in this case the class must be instantiated
 *		public function actualizarCreatedAt($save = false) {
 *			$this->_data['created_at'] = date("Y-m-d H:i:s");
 *			if($save) $this->save();
 *		}
 *		And now used like this:
 *		$tc = new tmpCategoria(array('tmp_descarga_id' => 1234,'nombre'=>'a'));
 *		$tc->actualizarCreatedAt()
 *
 */
class carritusORM implements \ArrayAccess
{
//	abstract protected static function get_dbh();

//	protected $columns; //Must be defined in all the subclasses
	protected $data;
	protected $changed_data;

	public function __construct( $data = array(), $is_new = true ) {
		$this->_data = $data;
		$this->changed_data = array();
		$this->is_new = $is_new;

		$columns = self::getColumns();
		foreach($columns as $col => $val)
			if(array_key_exists('default',$columns[$col]) && !isset($this->_data[$col]))
				$this->_data[$col] = $this->changed_data[$col] = $columns[$col]['default'];
	}

	public function __call( $name , $arguments ) {
		$cmd = substr($name,0,3);
		$key = underscore(substr($name,3));
		$columns = self::getColumns();

		if($cmd=='get')
		{
//			if(isset($columns[$key]))
//				return $this->_data[$key];
			return $this->get($key);
		}
		if($cmd=='set')
		{
			if(isset($columns[$key]))
				if(!isset($this->_data[$key]) || $this->_data[$key] != $arguments[0])
					$this->_data[$key] = $this->changed_data[$key] = $arguments[0];
			return $this;
		}
		else
		{
			if(is_callable($this,$name))
				return call_user_func_array($name, $arguments);

			if(isset($columns[$name]))
				return $this->_data[$name];
		}

        throw new \Exception( "Method '$name' not exist in class '" . get_class( $this ) . "'." );
	}
/*
	static public function __callStatic( $name , $arguments )
	{
		if(preg_match('/find(One)?(By)?(.*)/',$name,$match))
		{
			$one = isset($match[1]) && $match[1]=='One';
			$by = isset($match[2]) && $match[2]=='By';
			if(!$by)
				return self::findOneBy(array('id'=> $arguments[0]));
			else
			{
				print_r($match);

				$query = $match[3];
				$key = sfInflector::underscore(substr($query,3));


				if(!is_callable($name, false))
					throw new \Exception("$name is not callable - $callable_name ?\n");

				forward_static_call_array($name, $arguments);
			}
		}
	}
*/
	public function set($fieldName, $value = null) {
		$columns = self::getColumns();
		if(is_array($fieldName))
		{
			foreach( $fieldName as $fld => $val )
				if( isset($columns[$fld]) && (!isset($this->_data[$fld]) || $this->_data[$fld] != $val) )
					$this->_data[$fld] = $this->changed_data[$fld] = $val;
		}
		else if( !isset($this->_data[$fieldName]) || $this->_data[$fieldName] != $value )
			$this->_data[$fieldName] = $this->changed_data[$fieldName] = $value;

		return $this;
	}

	public function get($fieldName) {
	
		$columns = self::getColumns();
//		if($columns[$fieldName]['type']=='password')
//			return self::decodificar($this->_data[$fieldName]);

		if(array_key_exists($fieldName,$this->_data))
			return $this->_data[$fieldName];
		else if(!empty($this->relations) && array_key_exists(self::tableize($fieldName),$this->relations))
			return $this->relations[self::tableize($fieldName)];
		else{
			if(array_key_exists($fieldName,$columns))
			{
				if(array_key_exists('default',$columns[$fieldName]))
					return $columns[$fieldName]['default'];
				else
					return null;
			}
		}

		throw new \InvalidArgumentException('No existe el dato '.$fieldName);
	}

	public function toArray(){
		return $this->_data;
	}

	public static function getTableName() {
		//If any CarritusORM model class has different name than the table,
		//Uncomment this lines and define a variable $tableName with the table name.
//		$clase = get_called_class();
//		if(isset($clase::$tableName)) {
//			return self::tableize($clase::$tableName);
//		}

		$clase = get_called_class();
		if($pos = strrpos($clase,'\\'))
			$clase = substr($clase,$pos+1);

		return self::tableize($clase);
	}

	public static function tableize($word) {
		return strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', $word));
	}

	public function save() {
		try {

			$this->validate();

//			if(isset($this->_data['updated_at'])) {
//				$this->_data['updated_at'] = $this->changed_data['updated_at'] = date('Y-m-d H:i:s',strtotime('now'));
//			}

			return $this->doSave();

		} catch(carritusORMValidateException $e) {
//			echo $e->getMessage();exit;
			throw $e;
			return $e->getMessage();
		}
	}

	static protected function getColumns()
	{
		$clase = get_called_class();
		if(!isset($clase::$columns))
			throw new \Exception("The class '$clase' must define columns");

		return $clase::$columns;
	}

	protected function validate() {
		$columns = self::getColumns();
		if(is_null($columns) || empty($columns) || !is_array($columns)) {
			throw new \Exception('La class '.get_class($this).' no tiene el array columns definido');
		}
		$tableName = self::getTableName();
		if(empty($tableName) || !is_string($tableName)) {
			throw new \Exception('La class '.get_class($this).' no tiene un tableName correcto: '.$tableName);
		}

		//carritusORMValidateUndefinedColumnException
		$cols_undefined = array_diff_key($columns, $this->_data);
		foreach($cols_undefined as $col_undefined => $rules) {
			if(isset($columns[$col_undefined]['notnull']) && $columns[$col_undefined]['notnull'] == true && !array_key_exists('default',$columns[$col_undefined])) {
				throw new carritusORMValidateUndefinedColumnException($col_undefined);
			}
		}
		foreach($this->_data as $column => $value) {
			if($value===null && isset($columns[$column]['notnull']) && $columns[$column]['notnull'] == true && !array_key_exists('default',$columns[$column])) {
				throw new carritusORMValidateUndefinedColumnException($column);
			}
		}
/*
		//carritusORMValidateExtraDataException
		$extended_data = array_diff_key($this->_data, $columns);
		if(!empty($extended_data)) {
			throw new carritusORMValidateExtraDataException($extended_data);
		}
*/
		//carritusORMInvalidDataException
		foreach($columns as $column => $rules) {
			if(!isset($this->_data[$column]) && array_key_exists('default',$rules)) {
				$this->_data[$column] = $this->changed_data[$column] = $rules['default'];
			}
			if(!isset($this->_data[$column])) { //Si no pudiera estar vacio habria fallado arriba.
				continue;
			}
//			if(!isset($this->_data[$column]) && $column == 'id') { //Objeto nuevo
//				continue;
//			}
//			if(!isset($this->_data[$column]) && ($column == 'id' || $columns[$column]['notnull'] == true)) { //Objeto nuevo o no puede ser null
//				continue;
//			}
			switch($rules['type']) {
				case 'string':
				case 'text':
					//Test
/*					if(!is_string($this->_data[$column])) {
						throw new carritusORMInvalidDataException($column,'Not string',$this->_data[$column]);
					}
*/					if(isset($rules['length']) && $rules['length'] < strlen($this->_data[$column])) {
						throw new carritusORMInvalidDataException($column,sprintf('String too long (limit: %s / length: %s) %s:%s',$rules['length'],strlen($this->_data[$column]),get_class($this),print_r($this->_data,1)),$this->_data[$column]);
					}
					//Cast
					if($this->_data[$column] != ''.$this->_data[$column])
						$this->_data[$column] = $this->changed_data[$column] = ''.$this->_data[$column];
					break;

				case 'integer':
					//Test
					if(!is_finite($this->_data[$column])) {
						throw new carritusORMInvalidDataException($column,'Not finite',$this->_data[$column]);
					}
//					if(!is_integer($this->_data[$column])) {
//						throw new carritusORMInvalidDataException($column,'Not integer',$this->_data[$column]);
//					}
					if(isset($rules['length']) && $rules['length'] < strlen($this->_data[$column])) {
						throw new carritusORMInvalidDataException($column,sprintf('Integer too long (limit: %s / length: %s) %s:%s',$rules['length'],strlen($this->_data[$column]),get_class($this),print_r($this->_data,1)),$this->_data[$column]);
					}
					//Cast
					if($this->_data[$column] != intval($this->_data[$column]))
						$this->_data[$column] = $this->changed_data[$column] = intval($this->_data[$column]);
					break;

				case 'decimal':
					//Test
					if(!is_finite($this->_data[$column])) {
						throw new carritusORMInvalidDataException($column,'Not finite',$this->_data[$column]);
					}
					// if(round($this->_data[$column],$rules['scale']) != $this->_data[$column]) {
					// 	throw new carritusORMInvalidDataException($column,'Invalid scale',$this->_data[$column]);
					// }
					if(isset($rules['length']) && $rules['length'] < strlen(round($this->_data[$column],0))) {
						throw new carritusORMInvalidDataException($column,sprintf('Decimal too long (limit: %s / length: %s) %s:%s',$rules['length'],strlen(round($this->_data[$column],0)),get_class($this),print_r($this->_data,1)),$this->_data[$column]);
					}
					//Cast
					if($this->_data[$column] != floatval($this->_data[$column]))
						$this->_data[$column] = $this->changed_data[$column] = floatval($this->_data[$column]);
					break;

				case 'date':
				case 'timestamp':
					$fmt = $rules['type']=='date' ? 'Y-m-d' : 'Y-m-d H:i:s';

					if(strstr($this->_data[$column],'0000-00-00')!==false || !$this->_data[$column])
						break;

					//Test
					$format = isset($rules['format']) ? $rules['format'] : $fmt;
					$d = \DateTime::createFromFormat($format, $this->_data[$column]);

					if(!$d || $d->format($format) != $this->_data[$column])
					{
						// por si seteamos el valor con textos parseables con strtotime, por ejemplo 'now', '+1 day', etc..
						$value = date($format,strtotime($this->_data[$column]));
						$d = \DateTime::createFromFormat($format, $value);

						if(!$d || $d->format($format) != $value)
							throw new carritusORMInvalidDataException($column,'Invalid date (format: '.$format.') in '.$tableName.' #'.$this->getId(),$this->_data[$column]);
					}
					//Cast
					if($this->_data[$column] != $d->format("Y-m-d H:i:s"))
						$this->_data[$column] = $this->changed_data[$column] = $d->format("Y-m-d H:i:s");
					break;

				case 'boolean':
					//Test
					//... Nothing here... just parse the parse & go on
					//Cast
//					$this->_data[$column] = filter_var($this->_data[$column], FILTER_VALIDATE_BOOLEAN);
					break;

				case 'password':

					if(isset($rules['length']) && $rules['length'] < strlen($this->_data[$column])) {
						throw new carritusORMInvalidDataException($column,sprintf('String too long (limit: %s / length: %s) %s:%s',$rules['length'],strlen($this->_data[$column]),get_class($this),print_r($this->_data,1)),$this->_data[$column]);
					}
					//Cast
					if($this->_data[$column] != ''.$this->_data[$column])
						$this->_data[$column] = $this->changed_data[$column] = ''.$this->_data[$column];
					break;

				default:
					throw new \Exception('Invalid type '.$rules['type']);
			}
		}

		//Everything OK!
		return true;
	}

	static public function codificar($str){
		return md5(Config::get('token_seed','seed').$str);
	}

	static public function getEscapedData($data)
	{
		$dbh = self::get_dbh();
		$columns = self::getColumns();

		foreach($data as $column => &$value)
		{
			if($value===null)
				$value='NULL';
			else
			{
				// http://php.net/manual/en/pdo.constants.php, others: PDO::PARAM_NULL | PDO::PARAM_LOB
				switch($columns[$column]['type']) {
					case 'password':
						if(!$value)
							unset($data[$column]);
						else
							$value = self::codificar($value);
					case 'string':
					case 'text':
					case 'date':
					case 'timestamp':
						$parameter_type = \PDO::PARAM_STR;
						
						if(in_array($columns[$column]['type'],['timestamp','date']) && !$value && $column!='created_at')
							$value = 'NULL';
						else
							$value = $dbh->quote($value,$parameter_type);
						break;

					case 'integer':
					case 'decimal':
						$parameter_type = \PDO::PARAM_INT;
						if(!is_numeric($value)&&!is_bool($value))
							$value = 'NULL';
						break;
					case 'boolean':
						$parameter_type = \PDO::PARAM_BOOL;
						$value = $value ? 'TRUE' : 'FALSE';
						break;
					default:
						throw new \Exception("Invalid type on column '$column' {$columns[$column]['type']}");
				}
			}
		}

		return $data;
	}

	public function updateData($data)
	{
		$columns = self::getColumns();
		foreach($data as $key => $val)
			$this->_data[$key] = $this->changed_data[$key] = $val;

		return $this;
	}

	//INSERT or UPDATE depending on the $this->_data['id'] definition
	//@ToDo: If the model already exists, update only changed fields
	private function doSave() {

		$tableName = self::getTableName();
		$data = $this->is_new ? $this->_data : $this->changed_data;

		if(empty($data))
			return false;

		$data = self::setOrUnsetAutoField($data,'updated_at');
		if($this->is_new)
			$data = self::setOrUnsetAutoField($data,'created_at');

		$values = self::getEscapedData($data);

		//presave
		if($this->is_new)
		{
			$cols = '`'.implode('`,`',array_keys($values)).'`';
			$vals = implode(',',array_values($values));

			//preinsert
			$q = "INSERT INTO ".$tableName." (".$cols.") VALUES (".$vals.")";
			$id = self::query($q);
			if(!$id)
				throw new \Exception("Error on: $q");

			$this->_data['id'] = intval($id);

			$this->is_new = false;
			//postinsert
			//postsave
			return $id;
		}

		foreach($values as $col => $val)
			if($col!='id')
				$col_val[] = "$col = $val";

		//preupdate
		$q = "UPDATE ".$tableName." SET ".implode(',',$col_val)." WHERE id=".$this->_data['id'];

		//postupdate
		//postsave
		$rtn = self::query($q);
		if($rtn > 0) { //Only reset changed_data if the update has worked
			$this->changed_data = array();
		}
		return $rtn;
	}

	/**
	 * HYDRATION CONSTANTS
	 */
	const HYDRATE_RECORD            = 2; //As an instance
	const HYDRATE_ARRAY             = 3; //As an associative array

	public static function findOneBy($filters = array(), $joins = array(), $hydrationMode = self::HYDRATE_RECORD) {
		$query = self::generateQuery($filters);
		return self::findOneByQuery($query,$joins,$hydrationMode);
	}
	public static function findBy($filters = array(), $joins = array(), $hydrationMode = self::HYDRATE_RECORD) {
		$query = self::generateQuery($filters);
		return self::findByQuery($query, $joins, $hydrationMode);
	}
	public static function findAll($hydrationMode = self::HYDRATE_RECORD) {
		return self::findBy(array(),array(),$hydrationMode);
	}

	public static function findOneByQuery($query, $joins = array(), $hydrationMode = self::HYDRATE_RECORD) {
		$ret = self::findByQuery($query.' LIMIT 1',$joins,$hydrationMode);
		return count($ret)?$ret[0]:null;
/*
		$res = self::query('SELECT * FROM '.self::getTableName().($query?(' WHERE '.$query):'').' LIMIT 1');

		if(count($res) <= 0) return false;
		if($hydrationMode === self::HYDRATE_ARRAY) {
			return $res[0];
		} else if($hydrationMode === self::HYDRATE_RECORD) {
			$klassName = get_called_class();
			return new $klassName($res[0],false);
		}
		return new \InvalidArgumentException('Invalid hydrationMode: '.$hydrationMode);
*/
	}

	/**
	 * @param $where
	 * @param array $joins
	 *	$joins = array(
	 		'tmp_categoria' => array(
				'join' => 'left',
				'on' => 'tmp_descarga.id = tmp_categoria.tmp_descarga_id',
			)
		);
	 * @param int $hydrationMode carritusORM::HYDRATE_ARRAY || carritusORM::HYDRATE_RECORD
	 * @return array
	 * @throws InvalidArgumentException
	 */
	public static function findByQuery($where = false, $joins = array(), $hydrationMode = self::HYDRATE_RECORD) {
		if(!in_array($hydrationMode,array(self::HYDRATE_ARRAY,self::HYDRATE_RECORD))) {
			return new \InvalidArgumentException('Invalid hydrationMode: '.$hydrationMode);
		}
//		rbSupermercado::profile('');

		$q_select = $q_join = array();
		$char_sep = '___';

		$cols = array_keys(self::getColumns());
		$table = self::getTableName();
		foreach($cols as $col) {
			$q_select[] = "`{$table}`.`{$col}` as {$table}{$char_sep}{$col}";
		}

		foreach($joins as $table => $join) {

			$clase = camelize($table);
			$clase = self::get_class_on_namespace($clase);

			$cols = array_keys($clase::getColumns());

			foreach($cols as $col) {
				$q_select[] = "`{$table}`.`{$col}` as {$table}{$char_sep}{$col}";
			}
			$q_join[] = "{$join['join']} JOIN $table ON {$join['on']}";
		}

		$q_select = implode(',',$q_select);
		$q_join = implode(' ',$q_join);
		$q = 'SELECT '.$q_select.' FROM '.self::getTableName().' '.$q_join . ($where?(' WHERE ' . $where):'');
//		var_dump($q);
//		rbSupermercado::profile('construcción');

//		$q.=" order by `$table`.`id`";

		$res = self::query($q, self::HYDRATE_ARRAY);

//		rbSupermercado::profile('consulta');


		$rtn = array();
		$klassName = get_called_class();

		foreach($res as $row) {

			$data = array();
			foreach($row as $fld => $val) {
				list($table,$field) = explode($char_sep,$fld);
				$data[$table][$field] = $val;
			}

			if($hydrationMode === self::HYDRATE_ARRAY) {
				$obj = $data[self::getTableName()];
			} else { //$hydrationMode === self::HYDRATE_RECORD
				$obj = new $klassName($data[self::getTableName()],false);
			}
			unset($data[self::getTableName()]);

			foreach($data as $table => $relation_data) {
				if($hydrationMode === self::HYDRATE_ARRAY) {
					$obj[$table] = $relation_data;
				} else { //$hydrationMode === self::HYDRATE_RECORD
					$relKlassName = camelize($table);
					$relKlassName = self::get_class_on_namespace($relKlassName);
					$obj->relations[$table] = new $relKlassName($relation_data, false);
				}
			}

			$rtn[] = $obj;
		}
//		rbSupermercado::profile('hidratación');

		return $rtn;
	}

	public static function generateQuery($filters) {

		$q = array();
		if(!empty($filters))
		{
			foreach($filters as $key => $val)
			{
				if(is_string($val) || $val=='')
					$val = "'$val'";

				$key = str_replace('.','`.`',$key);
				$q[] = "`$key` = $val";
			}
		}
		
		return implode(' AND ', $q);
	}

	public static function count($where = false) {
//		$query = self::generateQuery($where);
//		return self::findOneByQuery($query,$joins,$hydrationMode);
		$q = 'SELECT count(*) as c FROM '.self::getTableName() . ($where?(' WHERE ' . $where):'').' LIMIT 1';
		$res = self::query($q);
		return intval($res[0]['c']);
	}


	//Taken for Mysql_Query_Tmp class


//	static $sql_execute = true;
	static $print_query = false;

	public static function query($query, $hydrationMode = self::HYDRATE_RECORD)
	{
		$items = self::do_query($query);

		$clase = get_called_class();
		if(isset($clase::$columns) && $hydrationMode == self::HYDRATE_RECORD)
			$items = self::hydrate($items); 

		return $items;
	}

	public static function queryMap($query,$key='id')
	{
		$items = self::query($query);
		return self::map($items,$key);
	}

	public static function queryOne($query)
	{
		if($items = self::query($query))
			return $items[0];
		return null;
	}

	public static function findByMap($data,$key='id')
	{
		$items = self::findBy($data);
		return self::map($items,$key);
	}

	public static function findByQueryMap($where = false, $key='id', $joins = array(), $hydrationMode = self::HYDRATE_RECORD)
	{
		$items = self::findByQuery($where,$joins,$hydrationMode);
		return self::map($items,$key);
	}

	public static function map($items,$keys='id')
	{
		if(!is_array($keys))
			$keys = array($keys);

		foreach($keys as $key)
			if(count($items) && !isset($items[0][$key]))
				throw new \Exception("no existe el campo $key");

		$ret = array();
		foreach($items as $item)
		{
			$value = &$ret;
			foreach($keys as $key)
				$value = &$value[$item[$key]];
			$value = $item;

			if(is_object($value))
				$value->is_new = false;
		 }

		return $ret;
	}

	static protected function get_dbh(){
		$clase = get_called_class();
		return $clase::get_dbh();
	}


/*

		for($retry=0;$retry<3;$retry++)
		{
			try {
				$sth = $dbh->query($query);
			} catch( Exception $e ){
				echo get_class($e)."\n".$e->getMessage();
				if( strstr($e->getMessage(),'try restarting transaction')!==false )
				{
					sleep(3);
					continue;
				}
				throw $e;
			}

			break;
		}
*/

	private static function do_query($query) {
		$dbh = self::get_dbh();
		if (self::$print_query) echo "$query".PHP_EOL;

		$query = trim(preg_replace('/\s+/',' ',$query));

		$max_reintentos = 5;
		for($retry=0;$retry<$max_reintentos;$retry++)
		{
			try {
	//			if (self::$sql_execute)
				{
					if(strncasecmp($query,'create ',7)==0){
						$sth = $dbh->exec($query);
						return true;
					}else{
						$sth = $dbh->query($query);
					}
				}
			} catch (\Exception $e) {
				file_put_contents( Config::get('LOG_PATH','log').'/query.err', $query );
				echo "\nERROR runing query: $query\n".$e->getMessage().";\n";
				throw $e;
			}

			$error_info = $dbh->errorInfo();

			// Que reintente solo si es un error de deadlock
			if( $sth )
				break;
			else
				if(preg_match("/Table '(.*?)' doesn't exist/",$error_info[2]??''))
				{
					$clase = get_called_class();
					if($clase::$create_table_str??null)
					{
						self::query($clase::$create_table_str);
						continue;
					}
				}		

			if(strncasecmp($query,'create ',7)==0)
				break;

			 // Si es un error de la query que no reintente
			if(!preg_match('/Deadlock|Lost connection|MySQL server has gone away/',$error_info[2]))
				break;

			echo "{$error_info[2]}, reintentando: $retry\n";
			sleep(3);

			$dbh = self::get_dbh();
		}

		if(!$sth)
		{
			pre($query);
			trigger_error($error_info[2]??'undefined error', E_USER_ERROR);return null;
/*
			$e = new \Exception($error_info[2]);
			pre("\n{$error_info[2]}\n$query\n\n".$e->getTraceAsString());
			return null;
*/
		}

		if(preg_match('/^select|^describe|^show|^CALL/i',$query)) {
			$arr = array();
			while( $row = $sth->fetch(\PDO::FETCH_ASSOC) )
				$arr[] = $row;
			return $arr;

		} else if(strncasecmp($query,'delete ',7)==0 || strncasecmp($query,'update ',7)==0) { // || strncasecmp($query,'insert ',7)==0
			return $sth->rowCount();

		} else {
			$id = $dbh->lastInsertId();
			return $id;

		}
	}

	public static function start_transaction() {
		$dbh = self::get_dbh();
		$sth = $dbh->query('START TRANSACTION');

		if( !$sth )
		{
			echo "\nPDO::errorInfo():\n";
			print_r($dbh->errorInfo());
		}
	}

	public static function end_transaction() {
		$dbh = self::get_dbh();
		$sth = $dbh->query('COMMIT');

		if( !$sth )
		{
			echo "\nPDO::errorInfo():\n";
			print_r($dbh->errorInfo());
		}
	}

	public static function rollback_transaction() {
		$dbh = self::get_dbh();
		$sth = $dbh->query('ROLLBACK');

		if( !$sth )
		{
			echo "\nPDO::errorInfo():\n";
			print_r($dbh->errorInfo());
		}
	}

	public static function lock_table($tabla) {
		$dbh = self::get_dbh();
		$sth = $dbh->query("LOCK TABLE $tabla");

		if( !$sth )
		{
			echo "\nPDO::errorInfo():\n";
			print_r($dbh->errorInfo());
		}
	}

	public static function unlock_tables() {
		$dbh = self::get_dbh();
		$sth = $dbh->query('UNLOCK TABLES');

		if( !$sth )
		{
			echo "\nPDO::errorInfo():\n";
			print_r($dbh->errorInfo());
		}
	}

	static public function lock()
	{
		self::lock_table(self::getTableName().' WRITE');
	}

	static public function unlock()
	{
		self::unlock_tables();
	}

	static public function insert($fields)
	{
		$clase = get_called_class();
		$rec = new $clase($fields);

		$rec->save();
		return $rec;
	}

	static public function inserts($arr,$force_same_id=false)
	{
		$clase = get_called_class();
		$table = self::getTableName();
		$columns = $clase::getColumns();
		if(!$force_same_id)
			unset($columns['id']);
		$fields = implode(',',array_keys($columns));

		$rows = array();
		foreach($arr as $row)
		{
			// para asegurar el mismo orden de columnas -> valores 
			$data = array();
			foreach($columns as $col => $field)
			{
				if(array_key_exists('default',$field) && !isset($row[$col]))
					$data[$col] = $field['default'];
				else
					$data[$col] = $row[$col];
			}

			$data = self::getEscapedData($data);

			$rows[] = implode(",",array_values($data));
		}
		$values = implode("),(",$rows);

		return self::query("insert into $table($fields) values($values)");
	}

	static function setOrUnsetAutoField($fieldset,$field)
	{
		$clase = get_called_class();
		$columns = $clase::getColumns();

		if(isset($columns[$field]))
		{
			if(isset($columns[$field]['auto']) && $columns[$field]['auto'])
				unset($fieldset[$field]);
			else
				$fieldset[$field] = $clase::now();
		}

		return $fieldset;
	}

	static public function now(){
		$now = self::query("select now() n");
		return $now[0]['n'];
	}

	static public function update($fieldset,$where)
	{
		$tableName = self::getTableName();

		$columns = self::getColumns();

		$fieldset = self::setOrUnsetAutoField($fieldset,'updated_at');
		$fieldset = self::getEscapedData($fieldset);

		$fieldsArr = array();
		foreach($fieldset as $fld => $val)
			$fieldsArr[] = "$fld = $val";

		$parsedFields = implode(',',$fieldsArr);

		try {
//			$this->validate();
			return self::query("UPDATE {$tableName} SET {$parsedFields} WHERE {$where}");
		} catch(carritusORMValidateException $e) {
			return $e->getMessage();
		}
	}

	public function delete()
	{
		self::deleteByQuery("id={$this['id']}");
		//unset($this); php7
	}

	static public function deleteById($id){
		return self::deleteByQuery("id={$id}");
	}

	static public function deleteByQuery($where){
		$tableName = self::getTableName();
		return self::query("DELETE FROM $tableName WHERE $where");
	}

	public function __toString(){
		return "<pre>".print_r($this->_data,1)."</pre>";
	}

	public function getEntityData(){
		return $this->_data;
	}

	static public function find($id){
		return self::findOneBy(array('id' => $id));
	}

	public function refresh(){
		$obj = self::find($this['id']);
		if($obj)
			$this->set($obj->data);
		return $obj;
	}

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->_data[] = $value;
        } else {
			$this->set($offset,$value);
//			$this->_data[$offset] = $value;
        }
    }

	public function offsetGet($offset) {
//		return isset($this->_data[$offset]) ? $this->_data[$offset] : null;
		return isset($this->_data[$offset]) ? $this->get($offset) : null;
	}

	public function offsetExists($offset) {
		return isset($this->_data[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->_data[$offset]);
	}

	static public function createQuery($alias=null){
		return new CarritusQuery(get_called_class(),$alias);
	}

	static public function hydrate($items)
	{
		$ret = array();

		if(!is_array($items))
			return $items;

		$cn = get_called_class();
		if($items)
			foreach($items as $item)
				$ret[] = new $cn($item);

		return $ret;
	}

	static protected function get_class_on_namespace($clase)
	{
		$namespace = preg_match_1('/(.*?)\\\[^\\\]+$/',get_called_class());
		if(!class_exists($ret = "\\$namespace\\{$clase}"))
			$ret = "\\$namespace\\".lcfirst($clase);

		return $ret;
	}
}
