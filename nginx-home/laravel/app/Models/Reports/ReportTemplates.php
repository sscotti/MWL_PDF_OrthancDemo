<?php

namespace App\Models\Reports;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use \DB;

/**
 * @property string     $modality
 * @property string     $type
 * @property boolean    $active
 * @property string     $url
 * @property string     $body_part
 * @property string     $subspecialty
 * @property string     $indication
 * @property string     $description
 * @property string     $markup_html
 * @property string     $markup_xml
 * @property string     $markup_xslt
 * @property string     $radlex_designator
 */
class ReportTemplates extends Model
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
    protected $table = 'report_templates';

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
        'modality', 'type', 'active', 'radreport_id', 'url', 'body_part', 'subspecialty', 'indication', 'description', 'markup_html', 'markup_xml', 'markup_xslt', 'radlex_designator'
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
        'modality' => 'string', 'type' => 'string', 'active' => 'boolean', 'url' => 'string', 'body_part' => 'string', 'subspecialty' => 'string', 'indication' => 'string', 'description' => 'string', 'markup_html' => 'string', 'markup_xml' => 'string', 'markup_xslt' => 'string', 'radlex_designator' => 'string'
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

    public static function radreport_templates_list(Request $request)  {

            Log::info($request);
            Log::info(Auth::user());

           if ($request->input('option') == 'getlist') {

            // array of array of reports
            // $templatearray['id'] = $row['id'];
            // $templatearray['description'] = $row['description'];
            // $templatearray['body_part'] = $row['body_part'];
            // $templatearray['subspecialty'] = $row['subspecialty'];
            // $templatearray['modality'] = $row['modality'];

            $responsearray["user"] = Auth::user()->reader_id;
            $templates = self::getModalityReportTemplates($request->input('modality'));
            //   print_r($templates);
            $html = '<option value="">Select a Template</option>';

            foreach ($templates as $template) {

            $html .= '<option value="' . $template['radreport_id'] . '"  data-type= "' . $template['type'] .'" >' . $template['subspecialty'] . " - " . $template['modality'] . " - " . $template['description'] . '</option>';
            }
            $responsearray["selectlist"] =  $html;
            echo json_encode($responsearray);
            }
    }

    public static function getModalityReportTemplates ($modality) {

        // $conn = DatabaseFactory::getFactory()->getConnection();
        // array of array of reports matching the $modality
        $array = array();
        $query = "SELECT * from report_templates WHERE modality = ? OR modality = 'ALL' AND active = 1  ORDER BY subspecialty, description";
        $params = [$modality];
//         $stmt = $conn->prepare($query);
//         $stmt->execute($parameters);
//         $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = DB::connection('mysql2')->select($query,$params);
        foreach ($result as $row) {
        $templatearray = [];
        $row = (array)$row;
        $templatearray['radreport_id'] = $row['radreport_id'];
        $templatearray['type'] = $row['type'];
        $templatearray['description'] = $row['description'];
        $templatearray['body_part'] = $row['body_part'];
        $templatearray['subspecialty'] = $row['subspecialty'];
        $templatearray['modality'] = $row['modality'];
        $array[] = $templatearray;
        }
        return $array;
    }

    public static function choose_template($studyuuid, $templateid) {

        //$conn = DatabaseFactory::getFactory()->getConnection();

        $query = "SELECT uuid, user from study_locks WHERE uuid = ? LIMIT 1";
        $params = [$studyuuid];
        $row = DB::connection('mysql2')->select($query,$params);

        if (count($row) > 0) {

             $user = $row[0]->user;
             $responsearray["user"] = $user;

        }

        $markup = self::getReportById($templateid);
        $responsearray["report"] = $markup;
        //$query="INSERT INTO  study_locks (uuid, user) VALUES (?, ?) ON DUPLICATE KEY UPDATE uuid = uuid";  // adds to table if it doesn't exist.
        //$parameters = [$_POST["uuid"], Session::get('user_id')];
        //$query = "SELECT uuid, user from study_locks WHERE uuid = ? AND user = ? LIMIT 1";
        //$params = [$studyuuid, Auth::user()->reader_id];
        //$row = DB::connection('mysql2')->select($query,$params);
        if (!isset($responsearray["user"])) {

            DB::connection('mysql2')->table('study_locks')->insert([
                'uuid' => $studyuuid,
                'user' => Auth::user()->reader_id
            ]);
            $responsearray["user"] = Auth::user()->reader_id;
        }
        echo json_encode($responsearray);

    }

    public static function getReportById ($id) {

      // html for the report, may have to encode with htmlentities, strips the \n's from the markup in the database, fetches the single row and column
      //$conn = DatabaseFactory::getFactory()->getConnection();
      $query = "SELECT markup_html from report_templates WHERE radreport_id = ?";
      $params = [$id];
      //$stmt = $conn->prepare($query);
      //$stmt->execute($params);
      //$result = $stmt->fetch(PDO::FETCH_ASSOC);
      $result = DB::connection('mysql2')->select($query,$params);
      Log::info($result);
      $result = $result[0]->markup_html;
      return str_replace("\n", '', $result);

    }

    // Relations ...
}
