<?php namespace TheDMSGrp\CommitContent\Console;

/**
 * Created by PhpStorm.
 * User: brandonbronisz
 * Date: 3/23/17
 * Time: 4:06 PM
 */
use Carbon\Carbon;
use Illuminate\Console\Command;
use TheDMSGrp\CommitContent\Models\Settings;
use TheDMSGrp\CommitContent\Services\GitManager;
use App;

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
    protected $description = 'pull site content from repository.';

    /**
     * Execute the console command.
     * @return void
     */
    public function fire()
    {
        $repo = Settings::get('content_repo');
        $branch = Settings::get('content_repo_sha');

        if (empty($repo)) {
            $this->error("Unable to retrieve content settings.");
            return;
        }

        if (!file_exists(themes_path() . '/.git')) {
            $this->warning("Repository is not yet cloned.");
            return;
        }

        $git = new GitManager($repo, themes_path());

        if (App::environment('production')) {

            $default_date = 'last tuesday 4:00am';
            $default_timezone = 'US/Eastern';

            $date = Settings::get('content_repo_production_date', $default_date) ?: $default_date;
            $timezone = Settings::get('content_repo_timezone', 'US/Eastern') ?: $default_timezone;

            if ($date) {

                try {

                    if (!empty($branch)) {
                        $git->fetch()->checkout($branch);
                        $git->pull($branch);
                    } else {
                        $carbonTimestamp = Carbon::parse($date, $timezone)->timestamp;
                        $git->fetch()->pullByDate($carbonTimestamp, 'master');
                    }

                } catch (\Exception $e) {
                    \Log::warning($e->getMessage());
                }

            }
        } else {
            $git->checkout('master');
            $git->pull('master');
        }
    }

}