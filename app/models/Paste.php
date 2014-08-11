<?php
namespace Schneenet\Vbin\Models;

class Album extends \Illuminate\Database\Eloquent\Model
{
	protected $with = array('previous');
	
	protected $dates = ['created_at', 'modified_at'];
	
	public function previous()
	{
		return $this->belongsTo("Schneenet\\Vbin\\Models\\Album", 'previous_id');
	}
	
	public function next()
	{
		return $this->hasOne("Schneenet\\Vbin\\Models\\Album", 'previous_id');
	}
}
