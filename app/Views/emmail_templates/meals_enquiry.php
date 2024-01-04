<!DOCTYPE html>
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1" name="viewport">
  <meta name="x-apple-disable-message-reformatting">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta content="telephone=no" name="format-detection">
  <title></title>
</head>

<body>
	<table width="100%" cellspacing="0" cellpadding="0" style="padding-left: 500px;">
		<?php if($status == 'reject'): ?>
        <tbody>
            <tr>
                <td class="" width="600" valign="top" align="left">
                    <table width="100%" cellspacing="0" cellpadding="0">
                <tbody>
                    <tr>
                    <td class="" align="left" style="font-size:0;padding-left: 150px;"><a href="#">
                        <img src="<?=base_url('public/assets/logo/logo_web.png')?>" alt style="display: block;" width="250">
                    </td>
                    </tr>
                            
                    <tr>
                    <td class="" bgcolor="" align="left" style="padding-top: 20px;">
                        <p >Dear <?= $user_name ?>, </p>
                        <p>  We are sorry to inform you that the Inquiry you choose is currently unavailable. </p>
                        <p>  We recommend you to place a different order or contact another provider. </p>
                        <p style="margin-top: 35px;"> If you face any issues further you can write us back. </p>
                    </td>
                    </tr>
                    
                    <tr>
                    <td class="" align="left" >
                        <p style="margin-top: 35px;"> Kind Regards,</p>
                        <p> Team Umrah Plus</p>
                        <p><a href="https://umrahplus.net"> https://umrahplus.net </a></p>
                    </td>
                    </tr>

                    <tr>
                    <td class"" align="left" >
                        <p>Download the app and enjoy</p>
                    </td>
                    </tr>

                    <tr>
                    <td align="left"><a href="#" rel="nofollow"><img src="https://tlr.stripocdn.email/content/guids/CABINET_e48ed8a1cdc6a86a71047ec89b3eabf6/images/92051534250512328.png" alt="App Store" class="adapt-img" title="App Store" width="133"></a>
                    <a href="#" rel="nofollow"><img class="adapt-img" src="https://tlr.stripocdn.email/content/guids/CABINET_e48ed8a1cdc6a86a71047ec89b3eabf6/images/82871534250557673.png" alt="Google Play" title="Google Play" width="133"></a>
                    </td>
                    </tr>

                </tbody>
                </table>
                </td>
            </tr>
        </tbody>
        <?php else: ?>
        <tbody>
            <tr>
                <td class="" width="600" valign="top" align="left">
                    <table width="100%" cellspacing="0" cellpadding="0">
                <tbody>
                    <tr>
                    <td class="" align="left" style="font-size:0;padding-left: 150px;"><a href="#">
                        <img src="<?=base_url('public/assets/logo/logo_web.png')?>" alt style="display: block;" width="250">
                    </td>
                    </tr>
                            
                    <tr>
                    <td class="" bgcolor="" align="left" style="padding-top: 20px;">
                        <p>Dear <?= $user_name ?>, </p>
                        <p>  Thanks for choosing Umrah Plus. </p>
                        <p>  Your enquiry has been received. Kindly check your e-mail. </p>
                        <p>  You will be contacted soon for further updates. </p>
                        <p style="margin-top: 35px;"> Bon appetit!</p>
                    </td>
                    </tr>
                    
                    <tr>
                    <td class="" align="left">
                        <p style="margin-top: 35px;"> Kind Regards,</p>
                        <p> Team Umrah Plus</p>
                        <p><a href="https://umrahplus.net"> https://umrahplus.net </a></p>
                    </td>
                    </tr>

                    <tr>
                    <td class"" align="left" >
                        <p>Download the app and enjoy</p>
                    </td>
                    </tr>

                    <tr>
                    <td align="left"><a href="https://apps.apple.com/us/app/umrahplus/id1640429513" rel="nofollow"><img src="https://tlr.stripocdn.email/content/guids/CABINET_e48ed8a1cdc6a86a71047ec89b3eabf6/images/92051534250512328.png" alt="App Store" class="adapt-img" title="App Store" width="133"></a>
                    <a href="https://play.google.com/store/apps/details?id=com.app.umacustomer" rel="nofollow"><img class="adapt-img" src="https://tlr.stripocdn.email/content/guids/CABINET_e48ed8a1cdc6a86a71047ec89b3eabf6/images/82871534250557673.png" alt="Google Play" title="Google Play" width="133"></a>
                    </td>
                    </tr>

                </tbody>
                </table>
                </td>
            </tr>
        </tbody>
        <?php endif; ?>    
	</table>
</body>
</html>