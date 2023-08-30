<?php
    $status = 0;
    $payment = [];

    if (isset($_GET['result'])) {
        $result = base64_decode($_GET['result']);
        $payment = json_decode($result);
        $status = $payment->status;
    }

    $statusLabel = statusLabel($status);

    function statusLabel($status)
    {
        if ($status == 5) {
            return [
                'class' => 'success',
                'label' => 'Giao dịch thành công'
            ];
        }

        if ($status == 8) {
            return [
                'class' => 'danger',
                'label' => 'Giao dịch đã bị hủy'
            ];
        }

        if ($status == 6) {
            return [
                'class' => 'danger',
                'label' => 'Giao dịch thất bại'
            ];
        }

        if ($status == 4 || $status == 2) {
            return [
                'class' => 'warning',
                'label' => 'Giao dịch đang xử lý'
            ];
        }

        if ($status == 15) {
            return [
                'class' => 'danger',
                'label' => 'Giao dịch hết hạn'
            ];
        }

        return [
            'class' => 'warning',
            'label' => 'Giao dịch đang xử lý'
        ];
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Kết quả thanh toán</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-6 offset-3 border mt-5 p-3" id="form_payment">
                    <?php if (!empty($payment)) : ?>
                        <h3 class="text-center text-<?= $statusLabel['class'] ?>"><?= $statusLabel['label'] ?></h3>
                        <hr>
                        <h3 class="text-center">Chi tiết giao dịch</h3>
                        <table class="table table-striped table-bordered">
                            <tbody>
                                <tr>
                                    <td width="30%">Payment No</td>
                                    <td><?= $payment->payment_no ?></td>
                                </tr>
                                <tr>
                                    <td width="30%">Invoice No</td>
                                    <td><?= $payment->invoice_no ?></td>
                                </tr>
                                <tr>
                                    <td width="30%">Amount</td>
                                    <td><?= number_format(intval($payment->amount), 0, ',', '.')?> <?= $payment->currency ?></td>
                                </tr>
                                <tr>
                                    <td width="30%">Method</td>
                                    <td><?= $payment->method ?></td>
                                </tr>
                                <tr>
                                    <td width="30%">Bank</td>
                                    <td><?= $payment->card_brand ?></td>
                                </tr>
                                <?php if(!empty($payment->failure_reason)) : ?>
                                    <tr>
                                        <td width="30%">Failure Reason</td>
                                        <td><?= $payment->failure_reason ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-center text-danger">Không tìm thấy thông tin giao dịch</p>
                    <?php endif ?>

                    <div class="text-center">
                        <a href="index.php" class="btn btn-primary">Trở về trang chủ</a>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
