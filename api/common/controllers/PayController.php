<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace api\common\controllers;

use common\models\Order;
use Yansongda\Pay\Pay;
use Yii;
use yii\db\Exception;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class PayController extends Controller
{
    public $enableCsrfValidation = false;
    /**
     * 支付宝支付
     * @SWG\Get(
     *     path="/pay/alipay",
     *     operationId="alipay",
     *     tags={"支付相关"},
     *     summary="支付宝支付",
     *     @SWG\Parameter(
     *         in="query",
     *         required=true,
     *         name="order_sn",
     *         description="订单号",
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="请求成功",
     *          ref="$/responses/Success",
     *     ),
     *     @SWG\Response(
     *          response="default",
     *          description="请求失败",
     *          ref="$/responses/Error",
     *     ),
     * )
     *
     * @return string
     * @throws NotFoundHttpException
     * @author thanatos <thanatos915@163.com>
     */
    public function actionAlipay()
    {
        $model = Order::findByOrderSn(Yii::$app->request->get('order_sn'));
        if (empty($model)) {
            throw new NotFoundHttpException();
        }

        $order = [
            'out_trade_no' => $model->order_sn,
            'total_amount' => $model->order_amount,
            'subject' => '图币充值'
        ];

        return Yii::$app->alipay->pay->web($order);

    }

    /**
     * 支付宝支付回调
     * @return string
     * @author thanatos <thanatos915@163.com>
     */
    public function actionAlipayNotify()
    {
        $alipay = Yii::$app->alipay->pay;

        try {
            $data = $alipay->verify();
            $trade_status = $data->get('trade_status');
            $out_trade_no = $data->get('out_trade_no');
            $trade_no = $data->get('trade_no');
            $total_amount = $data->get('total_amount');
            // 判断支付状态
            if ($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') {
                $order = Order::findByOrderSn($out_trade_no);
                if (empty($order)) {
                    throw new Exception('out_trade_no not exist');
                }
                $data = [
                    'trade_sn' => $trade_no,
                    'order_amount' => $total_amount,
                    'payment_name' => Order::PAYMENT_NAME_ALIPAY,
                ];
                // 处理支付回调
                if ($order->doSuccess($data)) {
                    return 'success';
                }

            } else {
                Yii::error($data->all(), 'Order');
            }

        } catch (\Throwable $e) {
            return $e->getMessage();
        }

    }


}