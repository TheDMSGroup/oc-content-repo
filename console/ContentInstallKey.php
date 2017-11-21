<?php namespace TheDMSGrp\ContentRepo\Console;

/**
 * Created by PhpStorm.
 * User: brandonbronisz
 * Date: 3/23/17
 * Time: 4:06 PM
 */
use Illuminate\Console\Command;
use TheDMSGrp\ContentRepo\Models\Settings;
use TheDMSGrp\ContentRepo\Services\GitManager;
use Illuminate\Support\Facades\Storage;

class ContentInstallKey extends Command
{
    /**
     * The console command name.
     * @var string
     */
    protected $name = 'content:ssh:install';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Add ssh key';

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

        // Private Key file.
        $privateFile = sprintf(
            '%s/%s',
            GitManager::$filePath,
            GitManager::$fileName
        );

        // Public key file.
        $publicFile = sprintf(
            '%s/%s%s',
            GitManager::$filePath,
            GitManager::$fileName,
            '.pem'
        );

        // Hosts file.
        $hostsFile = sprintf(
            '%s/%s',
            GitManager::$filePath,
            'known_hosts'
        );

        // Get the full path for each file.
        $realPath = storage_path('app');
        $publicFullPath = sprintf('%s/%s', $realPath, $publicFile);
        $privateFullPath = sprintf('%s/%s', $realPath, $privateFile);
        $hostsFullPath = sprintf('%s/%s', $realPath, $hostsFile);

        if (!file_exists($privateFullPath)) {
            Storage::put($privateFile, $privateKey);
            chmod($privateFullPath, 0400);
        }

        if (!file_exists($publicFullPath)) {
            Storage::put($publicFile, $publicKey);
            chmod($publicFullPath, 0400);
        }

        if (!file_exists($hostsFullPath)) {
            Storage::put($hostsFile, $hosts);
            chmod($hostsFullPath, 0644);
        }

        // Append known hosts.
        // $homeDir = env('HOME') ?: env('HOMEDRIVE') . env('HOMEPATH');
        // $sshDir = $homeDir . '/.ssh';
        // $knownHostsFile = $sshDir . '/known_hosts';
        // $keyContents = file_exists($knownHostsFile) ? file_get_contents($knownHostsFile) : null;
        // if (strpos($keyContents, $hosts) === false) {
        //     if (!is_dir($homeDir)) {
        //         mkdir($homeDir);
        //         chmod($homeDir, 0700);
        //     }
        //     if (!is_dir($sshDir)) {
        //         mkdir($sshDir, 0700);
        //     } else {
        //         chmod($sshDir, 0700);
        //     }
        //     file_put_contents($knownHostsFile, $hosts, FILE_APPEND);
        // }
    }

}