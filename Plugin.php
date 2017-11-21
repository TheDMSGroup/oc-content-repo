<?php namespace TheDMSGrp\ContentRepo;

use System\Classes\PluginBase;
use TheDMSGrp\ContentRepo\Services\GitManager;
use TheDMSGrp\ContentRepo\Models\Settings;
use Cms\Widgets\MediaManager;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Event, BackendAuth, Config;

/**
 * ContentRepo Plugin Information File
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
            'name'        => 'Content Repo',
            'description' => 'Keeps a themes and content in a repositories.',
            'author'      => 'TheDMSGrp',
            'icon'        => 'icon-git-square',
            'homepage'    => 'https://github.com/TheDMSGroup/oc-content-repo',
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
        $this->registerConsoleCommand('content:clone', 'TheDMSGrp\ContentRepo\Console\ContentClone');
        $this->registerConsoleCommand('content:pull', 'TheDMSGrp\ContentRepo\Console\ContentPull');
        $this->registerConsoleCommand('content:ssh:install', 'TheDMSGrp\ContentRepo\Console\ContentInstallKey');
        $this->registerConsoleCommand('content:checkout', 'TheDMSGrp\ContentRepo\Console\ContentCheckout');
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {

        // Menus
        if (class_exists('\RainLab\Pages\Classes\Menu')) {
            \RainLab\Pages\Classes\Menu::extend(function ($menu) {
                // Modify/Create
                $menu->bindEvent('model.afterSave', function () use ($menu) {
                    $this->ContentRepo('Modified', $menu->name);
                });

                // Delete
                $menu->bindEvent('model.afterDelete', function () use ($menu) {
                    $this->ContentRepo('Deleted', $menu->name);
                });
            });
        }

        // Static Page
        if (class_exists('\RainLab\Pages\Classes\Page')) {
            \RainLab\Pages\Classes\Page::extend(function ($page) {
                // Modify/Create
                $page->bindEvent('model.afterSave', function () use ($page) {
                    $this->ContentRepo('Modified', $page);
                });

                // Delete
                $page->bindEvent('model.afterDelete', function () use ($page) {
                    $this->ContentRepo('Deleted', $page);
                });
            });
        }

        // Page
        if (class_exists('\Cms\Classes\Page')) {
            \Cms\Classes\Page::extend(function ($page) {
                // Modify/Create
                $page->bindEvent('model.afterSave', function () use ($page) {
                    $this->ContentRepo('Modified', $page);
                });

                // Delete
                $page->bindEvent('model.afterDelete', function () use ($page) {
                    $this->ContentRepo('Deleted', $page);
                });
            });
        }

        // Media
        MediaManager::extend(function ($widget) {

            // Delete folder
            $widget->bindEvent('folder.delete', function ($path) {
                $this->ContentRepo('Deleted directory', $path);
            });

            // Delete file
            $widget->bindEvent('file.delete', function ($path) {
                $mediaClean = Config::get('cms.activeTheme') . '/media'
                    . str_replace('//', '/', $path);

                $this->deleteMedia('Deleted', $mediaClean);
            });

            // Rename folder
            $widget->bindEvent('folder.rename', function ($originalPath, $newPath) {
                $this->renameMedia($originalPath,
                    Config::get('cms.activeTheme') . '/media' . str_replace('//', '/', $newPath));
            });

            // Rename file
            $widget->bindEvent('file.rename', function ($originalPath, $newPath) {
                $this->renameMedia($originalPath,
                    Config::get('cms.activeTheme') . '/media' . str_replace('//', '/', $newPath),
                    'file');
            });

            // Create folder
            $widget->bindEvent('folder.create', function ($newFolderPath) {
                $mediaClean = Config::get('cms.activeTheme') . '/media'
                    . str_replace('//', '/', $newFolderPath);

                $this->ContentRepo('Created directory ', $mediaClean, true);
            });

            // Upload file
            $widget->bindEvent('file.upload', function ($filePath, UploadedFile $uploadedFile) {
                $mediaClean = Config::get('cms.activeTheme') . '/media'
                    . str_replace('//', '/', $filePath);

                $this->ContentRepo('Uploaded', $mediaClean);
            });

            // Move folder
            $widget->bindEvent('folder.move', function ($path, $dest) {
                $this->moveMedia($path,
                    Config::get('cms.activeTheme') . '/media' . str_replace('//', '/', $dest));
            });

            // Move file
            $widget->bindEvent('file.move', function ($path, $dest) {
                $this->moveMedia($path,
                    Config::get('cms.activeTheme') . '/media' . str_replace('//', '/', $dest),
                    'file');
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
                'class'       => 'TheDMSGrp\ContentRepo\Models\Settings',
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
     * @param $newFolder boolean
     * @param $recreate boolean
     * @param $movedDir string
     * @return void
     */
    private function ContentRepo($action, $file, $newFolder = false, $recreate = false, $movedDir = '')
    {
        $editor = BackendAuth::getUser();

        $gitMgr = new GitManager(
            Settings::get('content_repo'),
            themes_path());

        if ($newFolder) {
            file_put_contents($file . '/.gitignore', '!.gitignore');

            if ($recreate) {
                file_put_contents($file . $movedDir . '/.gitignore', '!.gitignore');
            }
        }

        $gitMgr
            ->commitAll(
                $editor->first_name . ' ' . $editor->last_name,
                $editor->email,
                $action,
                (is_string($file) ? $file : implode('.', $file->getFileNameParts())))
            ->push();
    }

    /**
     * Deletes media files
     *
     * @param $action string
     * @param $file string
     * @return void
     */
    private function deleteMedia ($action, $file)
    {
        $editor = BackendAuth::getUser();

        $gitMgr = new GitManager(
            Settings::get('content_repo'),
            themes_path());

        $gitMgr
            ->removeOne(
                $editor->first_name . ' ' . $editor->last_name,
                $editor->email,
                $action,
                $file)
            ->push();
    }

    /**
     * Renames or moves media files/folders; essentially a wrapper for ContentRepo(), but with the ability to create a
     * more robust commit message.
     *
     * @param $original string
     * @param $new string
     * @param $type string
     * @return void
     */
    private function renameMedia ($original, $new, $type = 'directory')
    {
        $this->ContentRepo("Renamed $type " . basename($original) . ' to', $new,
            ($type === 'directory' ? true : false));
    }

    /**
     * Created in the same vein as renameMedia()
     *
     * @param $original string
     * @param $new string
     * @param $type string
     * @return void
     */
    private function moveMedia ($original, $new, $type = 'directory')
    {
        $this->ContentRepo("Moved $type " . basename($original) . ' to', $new,
            ($type === 'directory' ? true : false),
            ($type === 'directory' ? true : false),
            $original);
    }
}
