<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
  <title>
  </title>
  <!--[if !mso]><!-->
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <!--<![endif]-->
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style type="text/css">
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }

        html {
            font-family: "Montserrat", Arial, Helvetica, sans-serif;
        }

        hr {
            display: block;
            height: 1px;
            border: 0;
            border-top: 1px solid #f1e4e4;
            margin: 1em 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 787px;
            margin: 0 auto;
            text-decoration: none;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: gray;
            margin: 5px 0px;
        }

        .price {
            font-weight: bold;
        }

        .wrap-header {
            display: flex;
            align-items: center;
        }

        .wrap-header img {
            width: 50px;
        }

        .header-left {
            font-size: 30px;
        }

        .quantity-email {
            color: gray;
        }

        .title-header {
            margin: 5px 0px;
        }

        .wrap-email {
            margin: 5px 0px;
            display: flex;
            justify-content: space-between;
            font-size: 13px;
        }

        .wrap-email-store {
            display: flex;
        }

        .title-email-store {
            font-weight: bold;
            margin-right: 2px;
        }

        .content {
            max-width: 600px;
            margin: 0 auto;
        }

        .wrap-content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo img {
            width: 120px;
        }

        .id-product {
            color: gray;
        }

        .text-content {
            color: gray;
            margin: 10px 0px;
        }

        .wrap-buttom {
            margin: 30px 0px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }

        .title-content {
            margin-bottom: 20px;
        }

        .wrap-buttom button {
            background-color: rgb(51, 103, 214);
            color: white;
            margin-right: 10px;
            padding: 15px;
            border-radius: 3px;
        }

        .wrap-buttom span {
            color: gray;
            margin-right: 15px;
        }

        .wrap-buttom a {
            color: rgb(51, 103, 214);
            text-decoration: none;
        }

        .info-order {
            margin: 20px 0px;
        }

        .info-order-title {
            margin: 10px 0px;
        }

        .item-info {
            margin: 10px 0px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .item-info-left {
            display: flex;
            align-items: center;
        }

        .item-info-left img {
            width: 50px;
        }

        .name-product {
            font-weight: bold;
        }

        .distribute-product {
            color: gray;
            font-size: 15px;
        }

        .total-price {
            margin: 20px 0px;
            display: flex;
            justify-content: end;
        }

        .total-price-right {
            width: 60%;
        }

        .wrap-price-before-discount {
            display: flex;
            justify-content: space-between;
            margin: 5px 0px;
        }

        .price-before-discount {
            margin-bottom: 15px;
        }

        .price-after-discount {
            margin: 20px 0px;
        }

        .info-customer {
            margin-bottom: 20px;
        }

        .title {
            color: gray;
        }

        .price-after {
            font-size: 20px;
            font-weight: bold;
        }

        .title-customer {
            margin: 20px 0px;
        }

        .wrap-info-customer {
            display: flex;
            margin-bottom: 50px;
        }

        .info-addess h4 {
            margin: 10px 0px;
        }

        .info-addess span {
            margin: 10px 0px;
            color: gray;
        }

        .info-payment h4 {
            margin: 10px 0px;
        }

        .info-payment span {
            margin: 10px 0px;
            color: gray;

        }

        .botton-email {
            margin: 20px 0px;
            font-size: 13px;
        }

        .botton-email span {
            color: gray;
            margin: 10px 0px;
        }

        .botton-email a {
            text-decoration: none;
            margin: 10px 0px;
        }
    </style>
    
    <title>Đơn hàng</title>
</head>

<body>
    <div class="container">


        <div class="content">
            <table style="width:100%">
                <tr>
                    <td>               
                        <div class="logo"><img
                        src="{{$store->logo_url}}" alt="">
                </div></td>
                    <td style="float: right;margin-top: 20px;"><div class="id-product">ÐƠN HÀNG #{{$order->order_code}}</div></td>
                </tr>
            </table>
            <h2 class="title-content">Cám ơn bạn đã mua hàng!</h2>
            <!-- <span class="text-content">
                Xin chào Dr, Chúng tôi đã nhận được đặt hàng của bạn và đã sẵn sàng để
                vận chuyển. Chúng tôi sẽ thông báo cho bạn khi đơn hàng được gửi đi.
            </span> -->
            <!-- <div class="wrap-buttom">
                <button>xem đơn hàng</button>
                <span>hoặc</span>
                <a href="#">Đến cửa hàng của chúng tôi</a>
            </div> -->
            <hr>
            <div class="info-order">
                <div class="info-order-title">
                    <h4>Thông tin đơn hàng</h4>
                </div>
                @foreach($order->line_items_at_time as $line_item)
                <table style="width:100%;margin-bottom:10px">
             
                    <tr>
                        <td>
                            <div class="item-info-left">
                                <img src="{{$line_item->image_url}}"
                                    alt=""
                                    style="margin-right: 15px;
                                    border: 1px solid #e5e5e5;
                                    border-radius: 8px;
                                    object-fit: cover;">
                                <div>
                                    <div class="name-product">{{$line_item->name}} × {{$line_item->quantity}}</div>
                                    @if( $line_item->distributes_selected != null && count($line_item->distributes_selected) > 0 )
                                     <div class="distribute-product">{{$line_item->distributes_selected[0]->name }}
                                        @if( $line_item->distributes_selected[0]->sub_element_distributes != null  )
                                                {{$line_item->distributes_selected[0]->sub_element_distributes}}
                                        @endif
                                    </div>
                                     @endif
                                </div>
                            </div>
                        </td>
                        <td style="float: right;"><div class="item-info-right price"> {{number_format($line_item->main_price)}}₫</div></td>
                    </tr>
                   
                </table>
                @endforeach
                <hr>
                <table style="width:100%">
                    <tr>
                        <td style="width:40%"></td>
                        <td>
                            <table style="border-spacing: 0;
                            border-collapse: collapse;
                            width: 100%;">
                                <tbody>
                                    <tr>
                                        <td style="padding: 5px 0">
                                            <p style="color: gray;">	Tổng giá trị sản phẩm</p>
                                        </td>
                                        <td style="padding: 5px 0;float:right">
                                            <strong>{{number_format($order->total_before_discount)}}₫</strong>
                                        </td>
                                    </tr>
                                    @if( $order->product_discount_amount > 0)
                                    <tr>
                                        <td style="padding: 5px 0">
                                            <p style="color: gray;">	Giảm giá sản phẩm</p>
                                        </td>
                                        <td style="padding: 5px 0;float:right">
                                            <strong>{{number_format($order->product_discount_amount)}}₫</strong>
                                        </td>
                                    </tr>
                                    @endif
                                    @if( $order->combo_discount_amount > 0)
                                    <tr>
                                        <td style="padding: 5px 0">
                                            <p style="color: gray;">	Giảm giá combo</p>
                                        </td>
                                        <td style="padding: 5px 0;float:right">
                                            <strong>{{number_format($order->combo_discount_amount)}}₫</strong>
                                        </td>
                                    </tr>
                                    @endif
                                    @if( $order->voucher_discount_amount > 0)
                                    <tr>
                                        <td style="padding: 5px 0">
                                            <p style="color: gray;">	Giảm giá voucher</p>
                                        </td>
                                        <td style="padding: 5px 0;float:right">
                                            <strong>{{number_format($line_item->voucher_discount_amount)}}₫</strong>
                                        </td>
                                    </tr>
                                    @endif
                                    @if( $order->balance_collaborator_used > 0)
                                    <tr>
                                        <td style="padding: 5px 0">
                                            <p style="color: gray;">	Sử dụng số dư CTV</p>
                                        </td>
                                        <td style="padding: 5px 0;float:right">
                                            <strong>{{number_format($line_item->balance_collaborator_used)}}₫</strong>
                                        </td>
                                    </tr>
                                    @endif
                                    @if( $order->bonus_points_amount_used > 0)
                                    <tr>
                                        <td style="padding: 5px 0">
                                            <p style="color: gray;">	Sử dụng xu</p>
                                        </td>
                                        <td style="padding: 5px 0;float:right">
                                            <strong>{{number_format($order->bonus_points_amount_used)}}₫</strong>
                                        </td>
                                    </tr>
                                    @endif
                                    @if( $order->total_shipping_fee > 0)
                                    <tr>
                                        <td style="padding: 5px 0">
                                            <p style="color: gray;">	Phí vận chuyển</p>
                                        </td>
                                        <td style="padding: 5px 0;float:right">
                                            <strong>{{number_format($order->total_shipping_fee)}}₫</strong>
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>  
                        </table>
                        <hr>
                        <table style="margin-top: 0px;width: 100%;">
                            <tr>
                                <td style="padding: 5px 0 0;color: gray;">Tổng cộng</td>
                                <td style="padding: 5px 0 0;float: right;font-size: 20px;font-weight: bold;">{{number_format($order->total_final)}}₫</td>
                            </tr>
                        </table>
                        </td>
                    </tr>
                </table>
                <hr>
                <div class="info-customer">
                    <div class="title-customer">
                        <h3>Thông tin khách hàng</h3>

                        <span>{{$order->phone_number}}</span>
                        <span>{{$order->customer == null ? "" : $order->customer->name}}</span>
                    </div>
                    
                        
                       
                        <div class="info-addess">
                            <h4>Ðịa chỉ giao hàng </h4> 

                            @if( $order->from_pos == false && $order->customer_address != null)
                            <span>{{$order->customer_address['address_detail'] ?? ""}} {{$order->customer_address['wards_name'] ?? ""}} {{$order->customer_address['district_name'] ?? ""}} {{$order->customer_address['province_name'] ?? ""}}
                             </span>
                             @endif
                        
                             @if( $order->from_pos == false)
                            <h4>Phương thức thanh toán</h4>
                            @endif

                            @if( $order->from_pos == false)
                            <span>{{$order->payment_method_name}}</span>
                            @endif

                            @if( $order->from_pos == true)
                            <span>Mua hàng tại quầy</span>
                            @endif

                        
                        </div>
                 
               
                </div>
                <hr>

                @if( $addressPickupExists != null) 
                <div class="botton-email">
                    <p><span>Nếu bạn có bất cứ câu hỏi nào, đừng ngần ngại liên lạc với chúng tôi tại</span><p>
                   
                 <p>  Shop:     {{$store->name}} <p>
                    <p> SĐT: {{$addressPickupExists->phone}} <p>
                        <p>  Địa chỉ:  {{$addressPickupExists->address_detail}}
                     {{$addressPickupExists->wards_name}}
                     {{$addressPickupExists->district_name}}
                     {{$addressPickupExists->province_name}} <p>
                </div>
                @endif
                
            </div>

            <center>
                <p style="margin:0;color:#777;line-height:150%;font-size:16px"><a style="font-size:10px;text-decoration:none;color:#999" href="https://ikitech.vn">powered by IKITECH</a></p>
                <a style="font-size:10px;text-decoration:none;color:#999" href="https://ikitech.vn">
                </a></center>

        </div>
    </div>
</body>

</html>