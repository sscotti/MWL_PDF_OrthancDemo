<?php

namespace App\Models\Orders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * @property string     $text
 * @property string     $hl7_code
 */
class OrderPriorities extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql2';

/**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'order_priorities';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'text', 'hl7_code'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'text' => 'string', 'hl7_code' => 'string'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [

    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = false;

    // Scopes...

    // Functions ...

    protected static function getOrderPriorities ($selected = null) {

        Log::info("getOrderPriorities");
        $priorities = self::all();
        $priority = '<option disabled selected value="">Select option</option>';

        foreach ($priorities as $row) {
            $priority .= '<option value="' . $row->hl7_code . '"';
            if ($selected == $row->hl7_code) {
                $priority.= ' selected';
            }
            $priority.= '>' . $row->text . '</option>';
        }
        Log::info($priority);
        return $priority;

    }

    // Relations ...
}
