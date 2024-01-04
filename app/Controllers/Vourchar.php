<?php

namespace App\Controllers;

use App\Models\ProviderModel;
use App\Models\AdminModel;
use App\Models\ActivitieImgModel;
use App\Models\ActivitieModel;
use App\Models\PackageModels;
use App\Models\MovmentModels;
use App\Models\VehicleModels;
use App\Models\ImagePackageModels;
use App\Models\BookingModel;
use App\Libraries\Report;

// headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class Vourchar extends BaseController
{

    public function getVourchar()
    {
        
        $BookingModel = new BookingModel();
        $Report = new Report();
        $booking_id = 1;
        $booking_data = $BookingModel->where('id',$booking_id)->first();
        // echo json_encode($booking_data);die();
        $data = array(
            'mode' => 'download',
            'health_data' => $booking_data
        );
        $Report->generate_voucher_pdf($data);


    }

} // class end
