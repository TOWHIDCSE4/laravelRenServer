<!doctype html><html xml:lang="vi" xmlns="http://www.w3.org/1999/xhtml">
<html lang="vi">
    <head>
        <title>Hóa đơn bán hàng {{$order->order_code}} - IKITECH.vn</title>
        <meta charset="utf-8" />
        <meta name="theme-color" content="#ab1d1d" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-status-bar-style" content="#ab1d1d" />
        <meta name="apple-mobile-web-app-title" content="Nhanh.vn" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <link rel="apple-touch-icon" href="/img/logo/nhanh_black.png" />
        <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
        <link rel="manifest" href="/manifest.json" />
        <link rel="preload" href="/min/?f=220429_13_L2Nzcy9hZG1pbi9mb250LmNzcywvY3NzL2FkbWluL2Jvb3RzdHJhcC5taW4uY3NzLC9jc3MvZm9udGF3ZXNvbWUubWluLmNzcywv/Y3NzL3ByaW50LmNzcw==/" as="style" />
        <link rel="stylesheet" href="/min/?f=220429_13_L2Nzcy9hZG1pbi9mb250LmNzcywvY3NzL2FkbWluL2Jvb3RzdHJhcC5taW4uY3NzLC9jc3MvZm9udGF3ZXNvbWUubWluLmNzcywv/Y3NzL3ByaW50LmNzcw==/" type="text/css" />
        <style>
            .cke {
                visibility: hidden;
            }
        </style>
        <style type="text/css">
            @font-face {
                font-family: "rbicon";
                src: url(chrome-extension://dipiagiiohfljcicegpgffpbnjmgjcnf/fonts/rbicon.woff2) format("woff2");
                font-weight: normal;
                font-style: normal;
            }
        </style>
    </head>
    <body class="printPage" style="display: block;">
        <div data-v-32478853="" class="odm_extension image_downloader_wrapper"><!----></div>

        <div style="margin: 0 auto; font-size: 14px; line-height: normal !important;">
            <div style="padding: 5px 2px;">
                <div style="padding: 8px 0; text-align: center;">
                    <h4 style="text-transform: uppercase;">Drhgfh</h4>
                </div>
                <div>
                    <p><b>Điện thoại:</b> 0835773520</p>
                    <p><b>Email:</b> fdgbbb@gmail.com</p>
                </div>
                <div style="padding: 8px 0 0; text-align: center; text-transform: uppercase;">
                    <h4>Hóa đơn bán hàng ({{$order->order_code}})</h4>
                </div>
                <br />
                <ul class="prlist">
                    <li style="list-style: none;" class="prHeader">
                        <span class="prtitle" style="width: 100%; display: inline-block; font-style: italic;"><b>Sản phẩm</b></span>
                        <span class="price" style="width: 25%; display: inline-block; padding-right: 3px; text-align: right; vertical-align: top;"><b>Giá</b></span>
                        <span class="qtt" style="width: 40%; display: inline-block; padding-right: 3px; text-align: right; font-style: italic; font-weight: bold; vertical-align: top;"><b>SL</b></span>
                        <span class="money" style="width: 25%; display: inline-block; padding-right: 3px; text-align: right; vertical-align: top;"><b>Tiền</b></span>
                        <span style="width: 100%; display: inline-block; border-bottom: dashed 1px;"></span>
                    </li>

                    @foreach($order->line_items_at_time as $line_item)
                    <li style="list-style: none;">
                        <span class="prName" style="width: 100%; display: inline-block; font-style: italic;">1. {{$line_item->name}}</span>
                        <span class="price" style="width: 25%; display: inline-block; padding-right: 3px; text-align: right; vertical-align: top;">{{$line_item->item_price}}</span>
                        <span class="qtt" style="width: 40%; display: inline-block; padding-right: 3px; text-align: right; font-style: italic; font-weight: bold; vertical-align: top;">{{$line_item->quantity}}</span>
                        <span class="money" style="width: 25%; display: inline-block; padding-right: 3px; text-align: right; vertical-align: top;">{{$line_item->item_price*$line_item->quantity}}</span>
                        <span style="width: 100%; display: inline-block; border-bottom: dashed 1px;"></span>
                    </li>
                    @endforeach 

                    <li style="font-weight: bold; list-style: none;">
                        <span class="price" style="width: 25%; display: inline-block; padding-right: 3px; text-align: right; vertical-align: top;"></span>
                        <span class="qtt" style="width: 40%; display: inline-block; padding-right: 3px; text-align: right; font-style: italic; font-weight: bold; vertical-align: top;">1</span>
                        <span class="money" style="width: 25%; display: inline-block; padding-right: 3px; text-align: right; vertical-align: top;">{{$order->total_final}}</span>
                        <span style="width: 100%; display: inline-block; border-bottom: dashed 1px;"></span>
                    </li>
                  
                </ul>
                <br />
                <div>
                    <p><b>Ngày:</b> {{$order->created_at}}</p>
                    <p><b>Thu ngân:</b></p>
                    <p><b>Khách hàng:</b> xxxxx 0868917689</p>
                </div>
                <div style="padding: 8px 0 5px; text-align: center; border-top: 1px solid #444;">
                    <h4 style="text-transform: uppercase;">Cám ơn quý khách đã mua hàng!</h4>
                </div>
            </div>
        </div>

        <a style="display: none;" id="defaultHrefBack" href="/pos/bill/detail?storeId=119288&amp;id=215447076"></a>
        <link
            rel="preload"
            href="/min/?f=220429_86_L2xpYi9qcXVlcnkvanF1ZXJ5LTMuNS4xLm1pbi5qcywvbGliL2pxdWVyeS9qcXVlcnkuY29va2llLmpzLC9saWIvYm9vdHN0cmFw/L2Jvb3RzdHJhcC5idW5kbGUubWluLmpzLC9saWIvbm90aWZpY2F0aW9uL3Bub3RpZnkubWluLmpzLC9saWIvZGF0ZXJhbmdlcGlj/a2VyL21vbWVudC5taW4uanMsL2xpYi9kYXRlcmFuZ2VwaWNrZXIvZGF0ZXJhbmdlcGlja2VyLmpzLC9saWIvZHJvcHpvbmUvZHJv/cHpvbmUubWluLmpzLC9saWIvc2VsZWN0Mi9zZWxlY3QyLm1pbi5qcywvbGliL3NlbGVjdDIvc2VsZWN0Mi5tdWx0aS1jaGVja2Jv/eGVzLmpzLC9saWIvc2VsZWN0Mi9pMThuL3ZpLmpzLC9saWIvYXV0b251bWVyaWMuanMsL2xpYi9ib290c3RyYXAtYXV0b2NvbXBs/ZXRlLmpzLC9qcy9hcHBUcmFuc2xhdG9yLmpzLC9qcy9hZG1pbi90aGVtZTEuanMsL2pzL2FkbWluL2xvYWREYXRhLmpzLC9qcy9h/cHBQYWdlRXZlbnRzLmpzLC9qcy9hcHBGdW5jdGlvbnMuanMsL2pzL2FwcExvY2F0aW9uLmpzLC9qcy9hcHBDb25zdHMuanMsL2pz/L21vYmlsZUFwcC5qcywvanMvYWRtaW4vYWRtaW4uanM=/"
            as="script"
        />
        <script
            type="text/javascript"
            src="/min/?f=220429_86_L2xpYi9qcXVlcnkvanF1ZXJ5LTMuNS4xLm1pbi5qcywvbGliL2pxdWVyeS9qcXVlcnkuY29va2llLmpzLC9saWIvYm9vdHN0cmFw/L2Jvb3RzdHJhcC5idW5kbGUubWluLmpzLC9saWIvbm90aWZpY2F0aW9uL3Bub3RpZnkubWluLmpzLC9saWIvZGF0ZXJhbmdlcGlj/a2VyL21vbWVudC5taW4uanMsL2xpYi9kYXRlcmFuZ2VwaWNrZXIvZGF0ZXJhbmdlcGlja2VyLmpzLC9saWIvZHJvcHpvbmUvZHJv/cHpvbmUubWluLmpzLC9saWIvc2VsZWN0Mi9zZWxlY3QyLm1pbi5qcywvbGliL3NlbGVjdDIvc2VsZWN0Mi5tdWx0aS1jaGVja2Jv/eGVzLmpzLC9saWIvc2VsZWN0Mi9pMThuL3ZpLmpzLC9saWIvYXV0b251bWVyaWMuanMsL2xpYi9ib290c3RyYXAtYXV0b2NvbXBs/ZXRlLmpzLC9qcy9hcHBUcmFuc2xhdG9yLmpzLC9qcy9hZG1pbi90aGVtZTEuanMsL2pzL2FkbWluL2xvYWREYXRhLmpzLC9qcy9h/cHBQYWdlRXZlbnRzLmpzLC9qcy9hcHBGdW5jdGlvbnMuanMsL2pzL2FwcExvY2F0aW9uLmpzLC9qcy9hcHBDb25zdHMuanMsL2pz/L21vYmlsZUFwcC5qcywvanMvYWRtaW4vYWRtaW4uanM=/"
        ></script>
        <script type="text/javascript" src="/cdn/_cache/location.vn.js?v=20220414_010059"></script>
        <script type="text/javascript" src="/lib/ckeditor4/ckeditor.js?2"></script>
        <script type="text/javascript">
            var usrCnf = {
                lang: "",
                locale: "",
            };
            if ("serviceWorker" in navigator) {
                window.addEventListener("load", () => {
                    navigator.serviceWorker.register("/js/service-worker.js").then((reg) => {
                        //console.log('Service worker registered.', reg);
                    });
                });
            }
            var appTheme = {
                button: { btnAddNewItem: "bg-success", btnAddSave: "bg-success", btnAddActive: "bg-success", btnFilter: "bg-teal-400", btnDelete: "text-danger", btnAction: "btn-primary" },
                background: { bgPaginatorActive: "bg-primary border-primary" },
                text: [],
                css: [],
            };
            var nhanhUserId = 2935295;
        </script>

        <script type="text/javascript">
            $(function () {
                /*
                 * 24/08/2020 Giapnv
                 * https://erp.nhanh.vn/nhiem-vu/sua-ban-in-sang-che-do-load-tung-phan/78361
                 * Khi có tham số printDialogMode thì ko in luôn mà chờ xử lý xong thì action đó tự xử lý
                 * */
                window.print();
               
                setTimeout(function () {
                    $(window).one("mousemove click keyup", function () {
                        if ($("#defaultHrefBack").attr("href")) {
                            window.location = $("#defaultHrefBack").attr("href");
                        } else {
                            window.close();
                        }
                    });
                }, 1500);
            });
        </script>
        <script async="" src="https://www.googletagmanager.com/gtag/js?id=G-L51RRFK6JT"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag() {
                dataLayer.push(arguments);
            }
            gtag("js", new Date());
            gtag("config", "G-L51RRFK6JT");
        </script>

        <div id="rememberry__extension__root" style="all: unset;"></div>
    </body>
</html>
