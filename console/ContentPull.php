<?php namespace TheDMSGrp\ContentRepo\Console;

use Illuminate\Console\Command;
use TheDMSGrp\ContentRepo\Models\Settings;
use TheDMSGrp\ContentRepo\Services\GitManager;

class ContentPull extends Command
{
    /**
     * The console command name.
     * @var string
     */
    protected $name = 'content:pull';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Pull/update site content';

    /**
     * Execute the console command.
     * @return void
     */
    public function fire()
    {
        $repo = Settings::get('content_repo');
        $privateKey = Settings::get('content_repo_private_key');
        $publicKey = Settings::get('content_repo_public_key');
        $hosts = Settings::get('content_repo_known_hosts');

        if (empty($repo) || empty($privateKey) || empty($publicKey) || empty($hosts)) {
            $this->error("Unable to retrieve content settings.");
            return;
        }

        if (!file_exists(themes_path()) || !is_dir(themes_path())) {
            mkdir(themes_path());
        }
        $git = new GitManager($repo, themes_path());
        $git->pull();

    }

}