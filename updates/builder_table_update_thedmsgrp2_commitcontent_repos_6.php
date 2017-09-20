<?php namespace TheDMSGrp\ContentRepo\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdatethedmsgrpcontentrepoRepos6 extends Migration
{
    public function up()
    {
        Schema::table('thedmsgrp_contentrepo_repos', function($table)
        {
            $table->string('deployment_type', 10)->default('automated')->change();
        });
    }
    
    public function down()
    {
        Schema::table('thedmsgrp_contentrepo_repos', function($table)
        {
            $table->string('deployment_type', 10)->default(null)->change();
        });
    }
}
