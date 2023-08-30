<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán chuyển hoàn</title>
    <link rel="stylesheet" href="/css/bank_pay.css">

</head>

<body>

    <div class="container">

        <h3>Thanh toán</h3>


        <p>Hãy chuyển số tiền <b> {{$total_final}}</b> với nội dung <b>{{ $order->order_code }}</b> tới một trong các ngân hàng sau</p>
        <h3></h3>


        @foreach($payment_guide as $bank_item)
        <div style='text-align: left;'>
            <div>
                <ul>
                    <li style='text-align: left;'>Tên tài khoản: {{$bank_item->account_name ?? ""}}</li>
                    <li style='text-align: left;'>Số tài khoản: {{$bank_item->account_number ?? "" }}</li>
                    <li style='text-align: left;' >Ngân hàng: {{$bank_item->bank ?? "" }}</li>
                    @if($bank_item->branch != null) <li style='text-align: left;'>Chi nhánh: {{$bank_item->branch ?? "" }}</li>   @endif
                </ul>
            </div>


        </div>
        @endforeach




</body>

</html>