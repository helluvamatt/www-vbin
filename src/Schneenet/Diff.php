<?php
namespace Schneenet;

/**
 * Create and parse unified diff
 *
 * Mostly adapted from here: https://github.com/matiasb/python-unidiff
 *
 * @author Matt Schneeberger
 *        
 */
class Diff
{

	# @@ (source offset, length) (target offset, length) @@ (section header)
	const RE_HUNK_HEADER = "/^@@ -(\d+)(?:,(\d+))? \+(\d+)(?:,(\d+))?\ @@[ ]?(.*)/";
	
	#   kept line (context)
	# + added line
	# - deleted line
	# \ No newline case (ignore)
	const RE_HUNK_BODY_LINE = "/^([- \\+\\\\])/";
	
	public $from;

	public $to;
	
	public $unifiedDiff;
	
	public $unifiedDiffLines;
	
	public $listing;

	public function __construct($from, $to)
	{
		$this->from = $from;
		$this->to = $to;
		
		$this->unifiedDiff = xdiff_string_diff($this->from, $this->to);
		$this->unifiedDiffLines = explode("\n", $this->unifiedDiff);
	}
	
	public function processForSideBySide()
	{
		$countLines = count($this->unifiedDiffLines);
		
		$fromLines = explode("\n", $this->from);
		$toLines = explode("\n", $this->to);
		
		$this->listing = array();
		
		$leftOffset = $rightOffset = 0;
		$leftReadPos = $rightReadPos = 0;
		
		// First pass: Go over each line in the unified diff, fill the left and right lists
		$deletingLinesQueue = array();
		$i = 0;
		while ($i < $countLines)
		{
			
			// Parse the hunk header
			$line = $this->unifiedDiffLines[$i];
			$matches = array();
			if (preg_match(Diff::RE_HUNK_HEADER, $line, $matches))
			{
				$leftOffset = $matches[1] - 1;
				$rightOffset = $matches[3] - 1;
				
				// Populate common lines for each side up to the first offset we have for this hunk
				while ($leftReadPos < $leftOffset && $rightReadPos < $rightOffset)
				{
					$this->listing[] = new Diff\DiffedLine(Diff\DiffedLine::MODE_CONTEXT, $fromLines[$leftReadPos], $toLines[$rightReadPos]);
					$rightReadPos++;
					$leftReadPos++;
				}
			}
			else if (preg_match(Diff::RE_HUNK_BODY_LINE, $line, $matches))
			{
				$action = $matches[1];
				$originalLine = substr($line, 1);
				if ($action == '+')
				{
					if (count($deletingLinesQueue) > 0)
					{
						// There is a delete pending, match it with this add to make a modify
						$this->listing[] = new Diff\DiffedLine(Diff\DiffedLine::MODE_MODIFIED, array_shift($deletingLinesQueue), $originalLine);
						
						// Keep track of read position
						$leftReadPos ++;
						$rightReadPos ++;
					}
					else 
					{
						// No delete pending, this is just an add
						$this->listing[] = new Diff\DiffedLine(Diff\DiffedLine::MODE_ADDED, "", $originalLine);
						
						// Keep track of read position
						$rightReadPos++;
					}
				}
				else if ($action == '-')
				{
					// Enqueue a delete to be handled later
					array_push($deletingLinesQueue, $originalLine);
				}
				else if ($action == ' ')
				{
					// Context detected, handle all deletes in the queue
					while (count($deletingLinesQueue) > 0)
					{
						$this->listing[] = new Diff\DiffedLine(Diff\DiffedLine::MODE_DELETED, array_shift($deletingLinesQueue), "");
						
						// Keep track of read positions
						$leftReadPos ++;
					}
					
					// Now add the context line
					$this->listing[] = new Diff\DiffedLine(Diff\DiffedLine::MODE_CONTEXT, $originalLine, $originalLine);
					
					// Keep track of read positions
					$leftReadPos ++;
					$rightReadPos ++;
					
				}
			}
				
			$i++;
		}
		
		// Continue adding common lines after the last hunk
		while ($leftReadPos < count($fromLines) && $rightReadPos < count($toLines))
		{
			$this->listing[] = new Diff\DiffedLine(Diff\DiffedLine::MODE_CONTEXT, $fromLines[$leftReadPos], $toLines[$rightReadPos]);
			$rightReadPos++;
			$leftReadPos++;
		}
	}
}

