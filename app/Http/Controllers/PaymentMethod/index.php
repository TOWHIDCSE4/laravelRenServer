<?php
    require_once('lib/HMACSignature.php');
    require_once('lib/MessageBuilder.php');

    #Thông tin cấu hình
	const MERCHANT_KEY = 'y1C0Nm'; // thông tin key của merchant wallet
    const MERCHANT_SECRET_KEY = '7mebyCRGt0lKM1vHuEhdveDX8wkiGkJ5D3W';  // thông tin secret key của merchant
    const END_POINT = 'https://sand-payment.9pay.vn';

    $invoiceNo = time() + rand(0,999999);
    $amount = rand(10000,99999);
    $description = "Mô tả giao dịch";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $http = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https://' : 'http://';
        $backUrl = "$http$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $returnUrl = str_replace('index.php', '', $backUrl);
        $time = time();
        //$time = 1648111631;

        $data = array(
            'merchantKey' => MERCHANT_KEY,           
            'time' => $time,
            'invoice_no' => $_POST['invoice_no'],
            'amount' => $_POST['amount'],
            'description' => $_POST['description'],
			
            'back_url' => $backUrl,
            'return_url' => "{$returnUrl}result.php",
        );		

        $message = MessageBuilder::instance()
            ->with($time, END_POINT . '/payments/create', 'POST')
            ->withParams($data)
            ->build();
			

        $hmacs = new HMACSignature();
        $signature = $hmacs->sign($message, MERCHANT_SECRET_KEY);

        $httpData = [
            'baseEncode' => base64_encode(json_encode($data, JSON_UNESCAPED_UNICODE)),
            'signature' => $signature,
        ];
        $redirectUrl = END_POINT . '/portal?' . http_build_query($httpData);
		echo '<pre>';
		print_r($data);	
		echo '<br/>';	
		echo '<hr/>';			
		print_r($message);			
		echo '<br/>';	
		echo '<hr/>';	
		var_dump($httpData);	
		echo '<br/>';	
		echo '<hr/>';	
		print_r($redirectUrl);	
		exit();
        //return header('Location: ' . $redirectUrl);
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Thanh toán hóa đơn</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" crossorigin="anonymous">
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-6 offset-3 border mt-5 p-3" id="form_payment">
                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="payment_no">Mã giao dịch</label>
                            <input type="text" class="form-control" name="invoice_no" value="<?= $invoiceNo ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="amount">Số tiền</label>
                            <input type="text" name="amount" class="form-control" value="<?= $amount ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Mô tả</label>
                            <input type="text" name="description" class="form-control" value="<?= $description ?>" placeholder="Mô tả giao dich" required>
                        </div>

                        <div class="action text-center">
                            <button type="submit" class="btn btn-success">Thanh toán</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>