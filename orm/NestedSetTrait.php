<?php

namespace orm;

trait NestedSetTrait
{
	/****************************\
	 * Table methods (static)	*
	\****************************/
	static public function insertNode(&$parent, $datos = array())
	{
		$item = parent::insert($datos);

		if(!$parent)
		{
			$last_root = self::findOneByQuery("true order by root_id desc");

			$item->set(array(
				'root_id' => $last_root->getRootId()+1,
				'lft' => 1,
				'rgt' => 2,
				'level' => 0
			))->save();
		}
		else
			$item = self::insertAsLastChildOf($parent, $item);

		return $item;
	}

	static public function insertAsLastChildOf(&$parent, $item)
	{
		$root_id	= $parent['root_id'];
		$lft		= $parent['rgt'];
		$rgt		= $parent['rgt']+1;
		$level		= $parent['level']+1;
		$id			= $item['id'];

for($retry=0;$retry<3;$retry++)
{
		self::start_transaction();
//		$table = self::getTableName();
//		self::lock_table("$table c WRITE");

		try{
			self::update(array(
				'lft' => 'lft+2',
			),"root_id = $root_id AND lft > $lft order by lft");

			self::update(array(
				'rgt' => 'rgt+2',
			),"root_id = $root_id AND rgt >= $lft order by lft");

			$item = self::find($id);

			$item->set(array(
				'root_id' 	=> $root_id,
				'lft' 		=> $lft,
				'rgt' 		=> $rgt,
				'level' 	=> $level,
			))->save();
		} catch(\Exception $e){
			echo "NestedSetTrait exception!! -> rollback";
			self::rollback_transaction();
			sleep(3);

			if($retry==2)
				throw $e;
		}

//		self::unlock_tables();
		self::end_transaction();
}
		$parent['rgt']+=2;

		return $item;
	}

	/********************\
	 * Class properties *
	\********************/
	public function delete()
	{
		$lft = $this['lft'];
		$rgt = $this['rgt'];
		$root_id = $this['root_id'];
		$diff = $rgt + 1 - $lft;

		self::deleteByQuery("lft >= $lft AND rgt <= $rgt");
		self::update(array('lft' => "lft - $diff"),"root_id = $root_id AND lft > $lft");
		self::update(array('rgt' => "rgt - $diff"),"root_id = $root_id AND rgt > $rgt");
	}

	public function getParent()
	{
		return self::findOneByQuery("lft < {$this['lft']} AND rgt > {$this['rgt']} AND root_id = {$this['root_id']} ORDER BY lft DESC");
	}

	public function getChilds()
	{
		$lft		= $this['lft'];
		$rgt		= $this['rgt'];
		$lvl		= $this['level']+1;
		$root_id	= $this['root_id'];
		return self::findByQuery("level={$lvl} AND lft>{$lft} AND rgt<{$rgt} AND root_id={$root_id}");
	}

	static public function getRoot($tmp_descarga_id)
	{
		return self::findOneByQuery("level=0 AND tmp_descarga_id={$tmp_descarga_id}");
	}

	public function getAncestors()
	{
		return self::findByQuery("lft < {$this['lft']} AND rgt > {$this['rgt']} AND root_id = {$this['root_id']} ORDER BY lft ASC");
	}

	public function getNextSibling()
	{
		return self::findOneByQuery("lft > {$this['lft']} AND root_id = {$this['root_id']} ORDER BY lft ASC");
	}

	public function getNext()
	{
		return self::findOneByQuery("lft = {$this['rgt']}+1 AND root_id = {$this['root_id']}");
	}

	public function findOneChildBy($filters)
	{
		$query = self::generateQuery($filters);
		return self::findOneByQuery("lft>{$this['lft']} AND rgt<{$this['rgt']} AND root_id={$this['root_id']} AND $query");
	}

	public function isLeaf()
	{
		return $this['rgt'] - $this['lft'] == 1;
	}

}
