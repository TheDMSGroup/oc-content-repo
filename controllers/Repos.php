<?php namespace TheDMSGrp\ContentRepo\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Repos extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController'];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'contentrepo'
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('thedmsgrp.contentrepo', 'contentrepo');
    }
}