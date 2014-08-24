<?php

namespace Schneenet\Diff;

class DiffedLine
{
	
	/**
	 * Invalid mode
	 */
	const MODE_NONE = -1;
	
	/**
	 * Left and right side are the same
	 */
	const MODE_CONTEXT = 0;
	
	/**
	 * Right side changed somewhat
	 */
	const MODE_MODIFIED = 1;
	
	/**
	 * Left side missing, new line on right
	 */
	const MODE_ADDED = 2;
	
	/**
	 * Right side missing, line on left deleted
	 */
	const MODE_DELETED = 3; 
	
	/**
	 * One of the MODE_* constants 
	 * @var int
	 */
	public $mode;
	
	/**
	 * Left side (from) line
	 * @var string
	 */
	public $leftSide;
	
	/**
	 * Right side (to) line
	 * @var string
	 */
	public $rightSide;
	
	/**
	 * Constructor
	 */
	public function __construct($mode = MODE_NONE, $leftSide = '', $rightSide = '')
	{
		$this->mode = $mode;
		$this->leftSide = $leftSide;
		$this->rightSide = $rightSide;
	}
}
