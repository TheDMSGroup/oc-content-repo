<?php

namespace TheDMSGrp\ContentRepo\Services;

use October\Rain\Exception\SystemException;
use Flash, Log, App;

/**
 * Class GitManager
 *
 * @package TheDMSGrp\ContentRepo\Services
 */
class GitManager
{

    public static $fileName = 'ContentRepo';
    public static $filePath = 'uploads/protected/.ssh';
    protected $repo;
    protected $path;

    public function __construct($repo, $path)
    {
        $this->repo = $repo;
        $this->path = $path;

        chdir($this->path);
    }

    /**
     * Wrapper method of executing Git commands
     *
     * @param string $statement Git command
     * @throws SystemException if the Git command failed
     * @return mixed
     */
    protected function handleExec($statement)
    {
        $output = false;

        if (App::environment('production') || App::environment('stage') || App::environment('staging')) {

            $git_prefix = '' .
                'GIT_SSH_COMMAND="' .
                'ssh -i ' . storage_path('app/' . self::$filePath . '/' . self::$fileName) . ' ' .
                '-o UserKnownHostsFile=' . storage_path('app/' . self::$filePath . '/known_hosts') . ' ' .
                '-o StrictHostKeyChecking=no"';

            $statement = str_replace('GITCMD', $git_prefix . ' git', $statement);
            Log::info('Git command running: ' . $statement);
            exec($statement, $output, $success);

            if ($success !== 0) {
                if (isset($output[2]) && $output[2] == 'nothing to commit, working tree clean') {
                    Log::info('Nothing to commit: ' . $statement, (array) $output);
                }
                else {
                    Log::info('Git command failed: ' . $statement, (array) $output);
                    throw new SystemException('Error with git command.  Please try again.');
                }
            }
            else {
                Log::info('Git command succeeded: ' . $statement, (array) $output);
            }
        }

        return $output;
    }

    /**
     * Commits all changes to the repository.
     *
     * @param $editorName
     * @param $editorEmail
     * @param $action
     * @param $file
     *
     * @return current object
     */
    public function commitAll($editorName, $editorEmail, $action, $file)
    {
        $editorName = str_replace("'", '', $editorName);
        $editorEmail = str_replace("'", '', $editorEmail);

        $this->handleExec(
            "GITCMD add -A && " .
            "GITCMD -c user.name='" . $editorName . "' " .
            "-c user.email='" . $editorEmail . "' " .
            "-c core.whitespace='blank-at-eol,blank-at-eof,space-before-tab,cr-at-eol' " .
            "-c core.autocrlf=false " .
            "commit -m '" . $action . ' ' . basename($file) . ".'"
        );
        return $this;
    }

    /**
     * Created in the same vein as commitAll(), this method commits media deletions one file at a time.
     *
     * @param $editorName
     * @param $editorEmail
     * @param $action
     * @param $file
     *
     * @return current object
     */
    public function removeOne($editorName, $editorEmail, $action, $file)
    {
        $editorName = str_replace("'", '', $editorName);
        $editorEmail = str_replace("'", '', $editorEmail);

        $this->handleExec(
            "GITCMD rm '" . $file . "' && " .
            "GITCMD -c user.name='" . $editorName . "' " .
            "-c user.email='" . $editorEmail . "' " .
            "-c core.whitespace='blank-at-eol,blank-at-eof,space-before-tab,cr-at-eol' " .
            "-c core.autocrlf=false " .
            "commit -m '" . $action . ' ' . basename($file) . ".'"
        );
        return $this;
    }

    /**
     * Pull changes to origin
     *
     * @return current object
     */
    public function pull($branch)
    {
        $this->handleExec("GITCMD pull origin $branch");
        return $this;
    }

    /**
     * fetch all branches
     * @return $this
     */
    public function fetch()
    {
        $this->handleExec('GITCMD fetch --all');
        return $this;
    }

    /**
     * Checkout a specific branch.
     * @param $branch
     * @return $this
     */
    public function checkout($branch)
    {
        $this->handleExec("GITCMD checkout $branch");
        return $this;
    }

    /**
     * @param $date
     * @return object
     */
    public function pullByDate($date, $branch)
    {

        $sha = $this->handleExec("GITCMD rev-list -n 1 --before=\"$date\" --date=raw $branch");

        if (!empty($sha[0]))
            $this->handleExec("GITCMD checkout -f $sha[0]");

        return $this;
    }

    /**
     * Pushes changes to origin
     *
     * @return object
     */
    public function push()
    {
        $this->handleExec('GITCMD push origin master');
        return $this;
    }

    /**
     * Clones content repository in themes/
     *
     * @return object
     */
    public function cloneRepo()
    {
        if (file_exists('.gitignore')) {
            unlink('.gitignore');
        }

        $this->handleExec('GITCMD clone ' . $this->repo . ' .');
        return $this;
    }
}
