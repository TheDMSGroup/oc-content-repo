<?php namespace TheDMSGrp\ContentRepo\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreatethedmsgrpcontentrepoRepos extends Migration
{
    public function up()
    {
        Schema::create('thedmsgrp_contentrepo_repos', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->text('theme');
            $table->text('repo');
            $table->text('private_key')->nullable();
            $table->text('public_key')->nullable();
            $table->text('known_hosts')->nullable();
            $table->boolean('scheduler')->default(1);
            $table->text('production_date')->nullable();
            $table->text('sha')->nullable();
            $table->text('timezone')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('thedmsgrp_contentrepo_repos');
    }
}
