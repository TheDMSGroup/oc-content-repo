<?php namespace TheDMSGrp\ContentRepo\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdatethedmsgrpcontentrepoRepos2 extends Migration
{
    public function up()
    {
        Schema::table('thedmsgrp_contentrepo_repos', function($table)
        {
            $table->string('branch', 40)->nullable()->default('master');
            $table->text('production_date')->default('now')->change();
            $table->string('sha', 40)->nullable()->unsigned(false)->default(null)->change();
            $table->text('deployment_type')->default('branch')->change();
        });
    }
    
    public function down()
    {
        Schema::table('thedmsgrp_contentrepo_repos', function($table)
        {
            $table->dropColumn('branch');
            $table->text('production_date')->default(null)->change();
            $table->text('sha')->nullable()->unsigned(false)->default(null)->change();
            $table->text('deployment_type')->default(null)->change();
        });
    }
}
