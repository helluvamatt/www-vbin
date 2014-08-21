<?php
namespace Schneenet\Vbin\Models;

class Paste extends \Illuminate\Database\Eloquent\Model
{
	protected $with = array('previous');
	
	protected $dates = ['created_at', 'modified_at'];
	
	public function previous()
	{
		return $this->belongsTo("Schneenet\\Vbin\\Models\\Paste", 'previous_id');
	}
	
	public function next()
	{
		return $this->hasOne("Schneenet\\Vbin\\Models\\Paste", 'previous_id');
	}
}
