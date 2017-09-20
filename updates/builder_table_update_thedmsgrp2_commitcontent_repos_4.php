<?php namespace TheDMSGrp\ContentRepo\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdatethedmsgrpcontentrepoRepos4 extends Migration
{
    public function up()
    {
        Schema::table('thedmsgrp_contentrepo_repos', function($table)
        {
            $table->text('deployment_type')->default('branch')->change();
            $table->renameColumn('date', 'schedule');
            $table->dropColumn('scheduler');
        });
    }
    
    public function down()
    {
        Schema::table('thedmsgrp_contentrepo_repos', function($table)
        {
            $table->text('deployment_type')->default(null)->change();
            $table->renameColumn('schedule', 'date');
            $table->boolean('scheduler')->default(1);
        });
    }
}
