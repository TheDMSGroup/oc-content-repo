<?php namespace TheDMSGrp\CommitContent;

use System\Classes\PluginBase;
use TheDMSGrp\CommitContent\Services\GitManager;
use TheDMSGrp\CommitContent\Models\Settings;
use RainLab\Pages\Classes\Page as StaticPage;
use Cms\Classes\Page;
use Cms\Widgets\MediaManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Event, BackendAuth;

/**
 * CommitContent Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'CommitContent',
            'description' => 'This plugin is responsible for committing changes made to Pages and Media to a remote content repository.',
            'author'      => 'thedmsgrp',
            'homepage'    => 'https://github.com/TheDMSGroup/commit-content'
        ];
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConsoleCommand('content:clone', 'TheDMSGrp\CommitContent\Console\ContentClone');
        $this->registerConsoleCommand('content:pull', 'TheDMSGrp\CommitContent\Console\ContentPull');
        $this->registerConsoleCommand('content:ssh:install', 'TheDMSGrp\CommitContent\Console\ContentInstallKey');
        $this->registerConsoleCommand('content:checkout', 'TheDMSGrp\CommitContent\Console\ContentCheckout');
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        // Static Page
        StaticPage::extend(function (StaticPage $page) {

            // Modify/Create
            $page->bindEvent('model.afterSave', function () use ($page) {
                $this->commitContent('Modified', $page);
            });

            // Delete
            $page->bindEvent('model.afterDelete', function () use ($page) {
                $this->commitContent('Deleted', $page);
            });
        });

        // Page
        Page::extend(function (Page $page) {

            // Modify/Create
            $page->bindEvent('model.afterSave', function () use ($page) {
                $this->commitContent('Modified', $page);
            });

            // Delete
            $page->bindEvent('model.afterDelete', function () use ($page) {
                $this->commitContent('Deleted', $page);
            });
        });

        // Media
        MediaManager::extend(function ($widget) {
            $widget->bindEvent('folder.delete', function ($path) {

            });
            $widget->bindEvent('file.delete', function ($path) {

            });
            $widget->bindEvent('folder.rename', function ($originalPath, $newPath) {

            });
            $widget->bindEvent('file.rename', function ($originalPath, $newPath) {

            });
            $widget->bindEvent('folder.create', function ($newFolderPath) {

            });
            $widget->bindEvent('folder.move', function ($path, $dest) {

            });
            $widget->bindEvent('file.move', function ($path, $dest) {

            });
            $widget->bindEvent('file.upload', function ($filePath, UploadedFile $uploadedFile) {

            });
        });
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'thedmsgrp.content.generalaccess' => [
                'tab'   => 'DMS Settings Sidebar',
                'label' => 'Content (GitHub repo) Administrative Interface'
            ]
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return [];
    }

    /**
     * @param string $schedule
     */
    public function registerSchedule($schedule)
    {
        if (Settings::get('content_repo_scheduler', false)) {
            $schedule->command('content:pull')->hourly();
        }
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'Settings',
                'description' => 'Configure content settings.',
                'category'    => 'Content',
                'icon'        => 'icon-cog',
                'class'       => 'TheDMSGrp\CommitContent\Models\Settings',
                'order'       => 500,
                'keywords'    => 'content configure',
                'permissions' => ['thedmsgrp.content.generalaccess']
            ]
        ];
    }

    /**
     * Commits changes to repository
     *
     * @param $action string
     * @param $file string
     * @return void
     */
    private function commitContent($action, $file)
    {
        $editor = BackendAuth::getUser();
        $gitMgr = new GitManager(
            Settings::get('content_repo'),
            themes_path());
        $gitMgr
            ->commit(
                $editor->first_name . ' ' . $editor->last_name,
                $editor->email,
                $action,
                implode('.', $file->getFileNameParts()))
            ->push();
    }
}
