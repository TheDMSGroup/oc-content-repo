<?php namespace TheDMSGrp\ContentRepo\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdatethedmsgrpcontentrepoRepos5 extends Migration
{
    public function up()
    {
        Schema::table('thedmsgrp_contentrepo_repos', function($table)
        {
            $table->string('cron', 32)->nullable()->default('* * * * *');
            $table->string('deployment_type', 10)->nullable()->unsigned(false)->default(null)->change();
            $table->dropColumn('schedule');
        });
    }
    
    public function down()
    {
        Schema::table('thedmsgrp_contentrepo_repos', function($table)
        {
            $table->dropColumn('cron');
            $table->text('deployment_type')->nullable()->unsigned(false)->default(null)->change();
            $table->string('schedule', 255)->nullable()->default('now');
        });
    }
}
