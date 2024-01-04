<?php

namespace App\Libraries;


use App\Libraries\pdf\FPDF;
use App\Libraries\pdf\ExFPDF;
use App\Libraries\pdf\EasyTable;

use CodeIgniter\I18n\Time;

use Config\Services;
use Exception;

class Report
{

   public function generate_voucher_pdf(array $data)
   {  

      $pdf = new ExFPDF();
      $pdf_file = 'public/vouchers/voucher_' . time();
      $pdf_path = $pdf_file . '.pdf';

      $pdf->SetLeftMargin(3);
      $pdf->SetRightMargin(3);
      $pdf->AddPage();
      $pdf->AddFont('Arimo', '', 'Arimo-Regular.php');
      $pdf->AddFont('Arimo', 'B', 'Arimo-Bold.php');
      $pdf->AddFont('Arimo', 'I', 'Arimo-Italic.php');
      $pdf->AddFont('Arimo', 'BI', 'Arimo-BoldItalic.php');

      $table = new EasyTable($pdf, '{12, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30,}', 'width:180; font-size:6; paddingY:4;');

      // $pdf->Image(base_url('public/assets/images/bg/welzo_test_report_draft.png'), 0, 0, 210, 300);


     

      ob_start();
      if (isset($data['mode']) && !empty($data['mode']) && $data['mode'] == 'download') {
         $pdf->Output($pdf_path, 'I', true); exit;
      } else if (isset($data['mode']) && !empty($data['mode']) && $data['mode'] == 'save') {
         $pdf->Output($pdf_path, 'F');
         return $pdf_path;
      }
   }
}
