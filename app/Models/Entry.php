<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Entry extends Model
{
    use HasFactory,SoftDeletes;

    protected $casts = [
    'created_at'  => 'date:Y-m-d',
    ];

    protected $fillable = ['id'];

    /**
   * The users that belong to the role.
   */
  public function label()
  {
      return $this->belongsToMany(Labels::class, 'entry_labels');
  }

  /**
 * Get the category
 */
  public function subCategory()
  {
      return $this->belongsTo(SubCategory::class,"category_id");
  }

  /**
 * Get the currency
 */
  public function currency()
  {
      return $this->belongsTo(Currency::class);
  }

  /**
 * Get the payments_type
 */
  public function account()
  {
      return $this->belongsTo(Account::class);
  }

  /**
 * Get the payments_type
 */
  public function transferTo()
  {
      return $this->belongsTo(Account::class, "transfer_id");
  }

  /**
 * Get the payments_type
 */
  public function payments_types()
  {
      return $this->belongsTo(PaymentsTypes::class);
  }

  /**
 * Get the payee
 */
  public function payee()
  {
      return $this->belongsTo(Payee::class);
  }
}
