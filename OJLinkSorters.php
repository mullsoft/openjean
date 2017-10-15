<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once("OJDatabase.php");

abstract class OJLinkSorter
{
	/**
	 * return -1, 0 or 1
	 * @param type $lnka
	 * @param type $lnkb
	 */
	public abstract function compare($lnka, $lnkb);
	
	private function compare1($lnka, $lnkb)
	{
		return $this->compare((object)$lnka, (object)$lnkb);
	}

	public function sort_links(&$link_array)
	{
		usort($link_array, array($this, "compare1"));
	}

}

class OJEntity_Access_LinkSorter extends OJLinkSorter
{
	public function compare($lnka, $lnkb)
	{
		$accessa = OJ_Row::get_single_value("entity_access", "accessed", "entities_id=".$lnka->to_entities_id);
		$accessb = OJ_Row::get_single_value("entity_access", "accessed", "entities_id=".$lnkb->to_entities_id);
		$ret = 0;
		if ($accessa == null)
		{
			if ($accessb != null)
			{
				$ret = 1;
			}
		}
		elseif ($accessb == null)
		{
			$ret = -1;
		}
		else
		{
			$tma = strtotime($accessa);
			$tmb = strtotime($accessb);
			$ret = $tma < $tmb?-1:($tma > $tmb?1:0);
		}
		return $ret;
	}

}

class OJ_Attribute_LinkSorter extends OJLinkSorter
{
	
	private $_page;
	private $_attname;
	public function __construct($page, $attname)
	{
		$this->_page = OJ_Pages::get_page_id($page);
		$this->_attname = $attname;
	}

	public function compare($lnka, $lnkb)
	{
		$cvala = $lnka->get_comparison_value($this->_page, $this->_attname);
		$cvalb = $lnkb->get_comparison_value($this->_page, $this->_attname);
		return OJ_Utilities::compare_comparison_values($cvala, $cvalb);
	}

}

class OJ_LinkSorterArray_LinkSorter extends OJLinkSorter
{
	private $_linksorters;
	
	public function __construct($linksorters)
	{
		$this->_linksorters = $linksorters;
	}
	
	public function compare($lnka, $lnkb)
	{
		$ret = 0;
		for ($n = 0; ($n < count($this->_linksorters)) && ($ret === 0); $n++)
		{
			$ls = $this->_linksorters[$n];
			$ret = $ls->compare($lnka, $lnkb);
		}
		return $ret;
	}

}

class OJ_Default_LinkSorter extends OJLinkSorter
{
	public function compare($lnka, $lnkb)
	{
		$orda = intval($lnka->ordinal);
		$ordb = intval($lnkb->ordinal);
		return $orda < $ordb?-1:($orda > $ordb?1:0);
	}

}

class OJ_Alphabetic_LinkSorter extends OJLinkSorter
{
	public function compare($lnka, $lnkb)
	{
		$nma = $lnka->name;
		$nmb = $lnkb->name;
		return strcmp($nma, $nmb);
	}

}

class OJ_Dictionary_LinkSorter extends OJLinkSorter
{
	public function compare($lnka, $lnkb)
	{
		$nma = $lnka->name;
		$nmb = $lnkb->name;
		return strcasecmp($nma, $nmb);
	}

}

class OJ_String_LinkSorter extends OJLinkSorter
{
	public function compare($lnka, $lnkb)
	{
		$nma = new OJ_String($lnka->name);
		$nmb = new OJ_String($lnkb->name);
		return OJ_String::compare($nma, $nmb);
	}

}
