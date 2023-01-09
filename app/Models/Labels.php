<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Labels extends Model
{
    use HasFactory;

    /**
   * The users that belong to the role.
   */
  public function entries()
  {
      return $this->belongsToMany(Entry::class, 'entry_labels');
  }


      /**
     * The users that belong to the role.
     */
    public function models()
    {
        return $this->belongsToMany(Models::class, 'model_labels');
    }
}
