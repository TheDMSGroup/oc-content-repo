<?php namespace TheDMSGrp\ContentRepo\Models;

use Model;

/**
 * Model
 */
class Repo extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    /*
     * Validation
     */
    public $rules = [
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'thedmsgrp_contentrepo_repos';
}