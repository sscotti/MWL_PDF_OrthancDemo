<?php

namespace App\Http\Controllers\MyControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Reports\ReportTemplates;
use App\Models\Reports\Reports;

class ReportsController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        //
    }
    protected function radreport_templates_list(Request $request) {

        return ReportTemplates::radreport_templates_list($request);

    }

    protected function getallhl7_reports(Request $request) {

        return Reports::getallhl7_reports($request->input('accession_number'));  // ($accession_number, $lastreport = null)

    }

    protected function choose_template(Request $request) {

        return ReportTemplates::choose_template($request->input('uuid'), $request->input('templateid'));

    }
}
