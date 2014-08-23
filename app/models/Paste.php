<?php
namespace Schneenet\Vbin\Models;

use Carbon\Carbon;
class Paste extends \Illuminate\Database\Eloquent\Model
{
		
	protected $fillable = ['previous_id', 'lang', 'title', 'data'];
	
	// Manually probe this relationship so we can limit the result set
	//protected $with = array('previous');
	
	// We never update records, we always create new ones
	protected $dates = ['created_at'];
	
	// Do not mutate dates (we handle that below)
	public function getDates()
	{
		return array();
	}
	
	public function previous()
	{
		return $this->belongsTo("Schneenet\\Vbin\\Models\\Paste", 'previous_id');
	}
	
	public function next()
	{
		return $this->hasOne("Schneenet\\Vbin\\Models\\Paste", 'previous_id');
	}
	
	public function getShareIdAttribute()
	{
		return str_pad(base_convert($this->id, 10, 16), 8, "0", STR_PAD_LEFT);
	}
	
	public function getCreatedAttribute()
	{
		return Carbon::parse($this->created_at);
	}
	
	public final static function history($model, $look_behind = 10, $look_ahead = 10)
	{
		$historyResult = array($model);
		Paste::_r_historyBack($model, $historyResult, $look_behind, 0);
		Paste::_r_historyForward($model, $historyResult, $look_ahead, 0);
		return $historyResult;
	}
	
	private final static function _r_historyBack($model, &$historyResult, $maxLookBehind, $iterCount)
	{
		$model->load('previous');
		if ($iterCount < $maxLookBehind && isset($model->previous))
		{
			$previous = $model->previous;
			array_unshift($historyResult, $previous);
			Paste::_r_historyBack($previous, $historyResult, $maxLookBehind, $iterCount + 1);
		}
	}
	
	private final static function _r_historyForward($model, &$historyResult, $maxLookAhead, $iterCount)
	{
		$model->load('next');
		if ($iterCount < $maxLookAhead && isset($model->next))
		{
			$next = $model->next;
			array_push($historyResult, $next);
			Paste::_r_historyForward($next, $historyResult, $maxLookAhead, $iterCount + 1);
		}
	}
}
