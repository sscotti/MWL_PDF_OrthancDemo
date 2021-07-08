<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string     $name
 * @property string     $address_1
 * @property string     $address_2
 * @property string     $city
 * @property string     $state
 * @property string     $country
 * @property string     $country_code_3
 * @property string     $postal_code
 * @property string     $phone_ctry
 * @property string     $phone
 * @property string     $fax_country
 * @property string     $fax
 * @property string     $website
 * @property string     $email
 * @property string     $facilitylogopath
 * @property string     $google_maps_url
 * @property string     $federal_ein
 * @property boolean    $service_location
 * @property boolean    $billing_location
 * @property string     $facility_currency
 * @property boolean    $accepts_assignment
 * @property int        $pos_code
 * @property string     $x12_sender_id
 * @property string     $attn
 * @property string     $domain_identifier
 * @property string     $facility_npi
 * @property string     $facility_taxonomy
 * @property string     $tax_id_type
 * @property string     $color
 * @property int        $primary_business_entity
 * @property string     $facility_code
 * @property boolean    $extra_validation
 * @property string     $mail_street
 * @property string     $mail_street2
 * @property string     $mail_city
 * @property string     $mail_state
 * @property string     $mail_country
 * @property string     $mail_zip
 * @property string     $oid
 * @property int        $orthanc_host
 */
class Facility extends Model
{

    protected $connection= 'mysql2';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'facility';

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
        'name', 'address_1', 'address_2', 'city', 'state', 'country', 'country_code_3', 'postal_code', 'phone_ctry', 'phone', 'fax_country', 'fax', 'website', 'email', 'facilitylogopath', 'google_maps_url', 'federal_ein', 'service_location', 'billing_location', 'facility_currency', 'billing_interest-rate', 'accepts_assignment', 'pos_code', 'x12_sender_id', 'attn', 'domain_identifier', 'facility_npi', 'facility_taxonomy', 'tax_id_type', 'color', 'primary_business_entity', 'facility_code', 'extra_validation', 'mail_street', 'mail_street2', 'mail_city', 'mail_state', 'mail_country', 'mail_zip', 'oid', 'orthanc_host'
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
        'name' => 'string', 'address_1' => 'string', 'address_2' => 'string', 'city' => 'string', 'state' => 'string', 'country' => 'string', 'country_code_3' => 'string', 'postal_code' => 'string', 'phone_ctry' => 'string', 'phone' => 'string', 'fax_country' => 'string', 'fax' => 'string', 'website' => 'string', 'email' => 'string', 'facilitylogopath' => 'string', 'google_maps_url' => 'string', 'federal_ein' => 'string', 'service_location' => 'boolean', 'billing_location' => 'boolean', 'facility_currency' => 'string', 'accepts_assignment' => 'boolean', 'pos_code' => 'int', 'x12_sender_id' => 'string', 'attn' => 'string', 'domain_identifier' => 'string', 'facility_npi' => 'string', 'facility_taxonomy' => 'string', 'tax_id_type' => 'string', 'color' => 'string', 'primary_business_entity' => 'int', 'facility_code' => 'string', 'extra_validation' => 'boolean', 'mail_street' => 'string', 'mail_street2' => 'string', 'mail_city' => 'string', 'mail_state' => 'string', 'mail_country' => 'string', 'mail_zip' => 'string', 'oid' => 'string', 'orthanc_host' => 'int'
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

    // Relations ...
    
    protected static function getPropertyValue($id, $property) {
    
        $facility = self::where('id', $id)->first();
        return $facility->$property;
    }


    protected static function letterHeader($id, $embedlogo = false) {

    	$facility = self::where('id', $id)->first();
    	$html =  '<style>
#reportheader{position:relative;width:800px;padding: 5px 5px 20px 5px;margin: 0px 0px 10px 0px;text-align:center;overflow:auto;margin:auto;}#reportheader #logo {height:60px;border:none;position:absolute;left:0;right:0;margin:auto;}#reportheader #floatleft{width:350px;display:inline-block;text-align:left;float: left;}#reportheader #floatright{width:max-content;text-align:left;float: right;padding-right:10px;}.letterheadlabel {display:inline-block;width:60px;text-align:right;margin-right:5px;}
</style>';
	$html .= '<div id = "reportheader"><div>';
	$logopath = isset($facility->facilitylogopath)?$facility->facilitylogopath:config('myconfigs.REPORTS_SITE_LOGO');
	if ($embedlogo == false) {
	    // base64 data url in the future
	    $html .= '<img id = "logo" height="60" src = "https://' . $_SERVER['HTTP_HOST']. $logopath . '" alt = "sitelogo">';
	}
	else {
	    $html .= '<img id = "logo" height="60" src = "' . $facility->facilitylogo_dataurl . '" alt = "sitelogo">';
	}

	$html .= '<div id = "floatleft">';
	$html .= isset($facility->name)?$facility->name . '<br>':"";
	$html .= isset($facility->address_1)?$facility->address_1 . '<br>':'';
	$html .= isset($facility->address_2)?$facility->address_2 . '<br>':'';
	$html .= isset($facility->city)?$facility->city . ', ':'';
	$html .= isset($facility->state) && $facility->state != "OS" ?$facility->state . ', ':'';
	$html .= isset($facility->country)?$facility->country . ' ':'';
	$html .= isset($facility->postal_code)?$facility->postal_code . ' ':'';
	$html .= '</div><div id = "floatright">';
	$html .= isset($facility->phone_ctry) && isset($facility->phone)?'<span class = "letterheadlabel">Phone:  </span>' . $facility->phone_ctry . ' ' .$facility->phone . '<br>':'';
	$html .= isset($facility->fax_country) && isset($facility->fax)?'<span class = "letterheadlabel">Fax:  </span>' . $facility->fax_country . ' ' .$facility->fax . '<br>':'';
	$html .= isset($facility->email)?'<span class = "letterheadlabel">Email:  </span>' . $facility->email . '<br>':'';
	$html .= isset($facility->website)?'<span class = "letterheadlabel">Web:  </span>' . $facility->website . '<br>':'';
	$html .= '</div></div></div>';
	return $html;

    }

    protected static function getMailingAddress($id) {
    
        $facility = self::where('id', $id)->first();
        $address = $facility->name;
    	$address .= (!empty($facility->mail_street))?'<br>' . $facility->mail_street:"";
    	$address .= (!empty($facility->mail_street2))?'<br>' . $facility->mail_street2:"";
    	$lastline = (!empty($facility->mail_city))?$facility->mail_city . ", ":"";
    	$lastline .= (!empty($facility->mail_state) && $facility->mail_state != "OS")?$facility->mail_state . ", ":"";
    	$lastline .= (!empty($facility->mail_country))?$facility->mail_country . " ":"";
    	$lastline .= (!empty($facility->mail_zip))?$facility->mail_zip:"";
    	$address .= (!empty($lastline))?'<br>' . $lastline:"";
    	return '<div>' . $address . '</div>';
    }

    protected static function getPhoneFAXEmailWeb($id) {
    
         $facility = self::where('id', $id)->first();
    	$html  = (!empty($facility->phone_ctry) && !empty($facility->phone))?'<div><div style = "display: inline-block;width: 40px;text-align: left;">Phone:  </div><div style = "display: inline-block;padding-left: 10px;">' . $facility->phone_ctry . ' ' . $facility->phone . "</div></div>":"";
    	$html .= (!empty($facility->fax_country) && !empty($facility->fax))?'<div><div style = "display: inline-block;width: 40px;text-align: left;">Fax:  </div><div style = "display: inline-block;padding-left: 10px;">' . $facility->fax_country . ' ' . $facility->fax . "</div></div>":"";
    	$html .= (!empty($facility->email))?'<div><div style = "display: inline-block;width: 40px;text-align: left;">E-mail:  </div><div style = "display: inline-block;padding-left: 10px;"><a href="mailto:Support<' . $facility->email . '?Support Request">' . $facility->email .  '</a></div></div>':"";
    	$html .= (!empty($facility->website))?'<div><div style = "display: inline-block;width: 40px;text-align: left;">Web:  </div><div style = "display: inline-block;padding-left: 10px;"><a href="' . $facility->website .  '" target="_blank">' . $facility->website . '</a></div></div>':"";
    	return '<div>' . $html . '</div>';
    }
}
