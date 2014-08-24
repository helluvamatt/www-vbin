<?php
namespace Schneenet\Vbin\Models;

use Carbon\Carbon;

class BaseModel extends \Illuminate\Database\Eloquent\Model
{
	public $incrementing = false;
	
	// Do not mutate dates
	protected $dates = array();
	
	public function getDates()
	{
		return $this->dates;
	}

	
	public function getCreatedAttribute()
	{
		return Carbon::parse($this->created_at);
	}
	
	public function getUpdatedAttribute()
	{
		return Carbon::parse($this->updated_at);
	}
}