<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $uploader_id
 * @property string $uploader_name
 * @property string $date_time_key
 * @property int $counter_index
 * @property boolean $aborted_flag
 * @property mixed $utc_hit_json
 * @property mixed $study_uuid_json
 */
class DCMUploadTemp extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'dicom_uploads_temp';

    /**
     * @var array
     */
    protected $fillable = ['uploader_id', 'uploader_name', 'date_time_key', 'counter_index', 'aborted_flag', 'utc_hit_json', 'study_uuid_json'];

}
