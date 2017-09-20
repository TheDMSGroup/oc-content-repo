<?php namespace TheDMSGrp\ContentRepo\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdatethedmsgrpcontentrepoRepos7 extends Migration
{
    public function up()
    {
        Schema::table('thedmsgrp_contentrepo_repos', function($table)
        {
            $table->string('cron', 32)->default('*/15 * * * *')->change();
            $table->renameColumn('repo', 'url');
            $table->dropColumn('timezone');
        });
    }
    
    public function down()
    {
        Schema::table('thedmsgrp_contentrepo_repos', function($table)
        {
            $table->string('cron', 32)->default('* * * * *')->change();
            $table->renameColumn('url', 'repo');
            $table->string('timezone', 255)->nullable();
        });
    }
}
