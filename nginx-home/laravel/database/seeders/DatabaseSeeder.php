<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // User::factory(10)->create();
        
        DB::table('orthanc_hosts')->insert([
        
            'AET' => 'DEMO',
            'nginx_admin_url' => '',
            'api_url' => 'http://pacs:8042/',
            'proxy_url' => '/pacs',
            'osimis_viewer_link' => '/stoneviewer?',
            'server_check' => 'http://pacs:8042/tools/now-local',
            'server_name' => 'DEMO',
            'osimis_viewer_name' => 'DEMO',
            'domain' => 'orthanc.test'
        ]);
        
    }
}
