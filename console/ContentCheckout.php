<?php namespace TheDMSGrp\CommitContent\Console;

use Illuminate\Console\Command;
use TheDMSGrp\CommitContent\Models\Settings;
use TheDMSGrp\CommitContent\Services\GitManager;
use Symfony\Component\Console\Input\InputArgument;

class ContentCheckout extends Command
{
    /**
     * The console command name.
     * @var string
     */
    protected $name = 'content:checkout {branch}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Clone site content repository.';

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

        $git = new GitManager($repo, themes_path());
        $git->fetch();
        $git->checkout($this->argument('branch'));

    }

    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['branch', InputArgument::REQUIRED, 'Branch name'],
        ];
    }


}