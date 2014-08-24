<?php
namespace Schneenet\Vbin\Models;

use Carbon\Carbon;

class PasteRevision extends BaseModel
{
	protected $fillable = ['lang', 'title', 'data'];
	
	public function parent()
	{
		return $this->belongsTo('Schneenet\\Vbin\\Models\\Paste');
	}
	
	public function createId()
	{
		$this->id = \Schneenet\HashId::create(time() . $this->data);
		return $this->id;
	}

}
