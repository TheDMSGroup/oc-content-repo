<?php namespace TheDMSGrp\ContentRepo\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdatethedmsgrpcontentrepoRepos3 extends Migration
{
    public function up()
    {
        Schema::table('thedmsgrp_contentrepo_repos', function($table)
        {
            $table->string('date', 255)->nullable()->default('now');
            $table->string('theme', 255)->nullable(false)->unsigned(false)->default(null)->change();
            $table->string('repo', 2048)->nullable(false)->unsigned(false)->default(null)->change();
            $table->string('timezone', 255)->nullable()->unsigned(false)->default(null)->change();
            $table->text('deployment_type')->default('branch')->change();
            $table->dropColumn('production_date');
        });
    }
    
    public function down()
    {
        Schema::table('thedmsgrp_contentrepo_repos', function($table)
        {
            $table->dropColumn('date');
            $table->text('theme')->nullable(false)->unsigned(false)->default(null)->change();
            $table->text('repo')->nullable(false)->unsigned(false)->default(null)->change();
            $table->text('timezone')->nullable()->unsigned(false)->default(null)->change();
            $table->text('deployment_type')->default(null)->change();
            $table->text('production_date')->nullable();
        });
    }
}
