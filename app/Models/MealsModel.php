<?php

namespace App\Models;

use CodeIgniter\Model;

class MealsModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'tbl_meals';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['title','cuisine_id','menu_url','no_of_person','cost_per_meals','cost_per_day','meals_type','meals_service','pickup_address','img_url_1','img_url_2','img_url_3','thumbnail_url','status','provider_id','created_date','updated_date','cities','provider_lat','provider_long'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
