<?php
namespace Schneenet\Vbin\Models;

use Carbon\Carbon;

class Paste extends BaseModel
{	
	public function revisions()
	{
		return $this->hasMany('Schneenet\\Vbin\\Models\\PasteRevision');
	}
}
