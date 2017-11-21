<?php namespace TheDMSGrp\ContentRepo\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatethedmsgrpcontentrepoRepos extends Migration
{
    public function up()
    {
        Schema::create('thedmsgrp_contentrepo_repos', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('theme', 255)->nullable(false)->unsigned(false)->default(null);
            $table->string('url', 2048)->nullable(false)->unsigned(false)->default(null);
            $table->text('private_key')->nullable();
            $table->text('public_key')->nullable();
            $table->text('known_hosts')->nullable();
            $table->string('sha', 40)->nullable()->unsigned(false)->default(null);
            $table->string('deployment_type', 10)->nullable()->unsigned(false)->default('automated');
            $table->string('branch', 40)->nullable()->default('master');
            $table->string('cron', 32)->nullable()->default('*/15 * * * *');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('thedmsgrp_contentrepo_repos');
    }
}
