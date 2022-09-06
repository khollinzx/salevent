<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Services\Helper;
use App\Services\JsonAPIResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Exception;

class OrderController extends Controller
{
    protected $orderModel;
    protected $orderDetailsModel;
    protected $cartModel;

    /**
     * OrderController constructor.
     * @param Order $order
     * @param Cart $cart
     * @param OrderDetail $orderDetail
     */
    public function __construct(Order $order, Cart $cart, OrderDetail $orderDetail)
    {
        $this->orderModel = $order;
        $this->orderDetailsModel = $orderDetail;
        $this->cartModel = $cart;
    }

    /** Processes a purchased item/s and stores selected cart items
     * to the orderDetails table
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function processCartItemToOrder(Request $request)
    {
        $userId = $this->getUserId();
        $receiptCode = Helper::generateTicketNumber();

        /**
         * Set the Validation rules
         */
        $Validation = Validator::make($request->all(), [
            "total_items" => 'required|integer',
            "total_price" => 'required|integer',
            "balance" => 'required|integer',
            "payment_type_id" => 'required|integer',
            "reference_no" => 'sometimes|required|integer',
            "amount_paid" => 'required|integer'
        ]);

        /**
         * Returns validation errors if any
         */
        if ($Validation->fails())
            return JsonAPIResponse::sendErrorResponse($Validation->errors()->first());

        $fields = [
            "total_items" => $request->total_items,
            "total_price" => $request->total_price,
            "balance" => $request->balance,
            "payment_type_id" => $request->payment_type_id,
            "reference_no" => $request->reference_no ?? 0,
            "amount_paid" => $request->amount_paid
        ];

        try {

            $cartItems = $this->cartModel::fetchAllCartItemBySalesId($userId);

            $newOrder = $this->orderModel->initializeNewOrder($userId, $receiptCode, $fields);

            if(!$this->orderDetailsModel->initializeNewOrderDetails($newOrder->id, $receiptCode, $cartItems))
                return JsonAPIResponse::sendErrorResponse('Error storing cart items to OrderDetail');

            $this->cartModel::clearItemsFromCart($cartItems);
            return JsonAPIResponse::sendSuccessResponse('Order was successfully created');

        } catch (Exception $e) {

            return JsonAPIResponse::sendErrorResponse($e->getMessage());
        }

    }

    /** fetches all the ordered Items pertaining to a specific sale respresentative
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllOrderedItems()
    {
        $userId = $this->getUser();
        $order = $this->orderModel->getAllOrderedItemsByUserId($userId);

        if(count($order) === 0)
            return JsonAPIResponse::sendErrorResponse("No record found");

        return JsonAPIResponse::sendSuccessResponse('Order specific to a Sales Representative.', $order);
    }

    /** fetches an order record along with items been ordered
     * @param $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderedItemWithDetails($orderId)
    {
        $userId = $this->getUser();

        if(!$this->orderModel::findById($orderId))
            return JsonAPIResponse::sendErrorResponse("Invalid selection made");

        $order = $this->orderModel->getOrderRecord($orderId);
        $items = $this->orderDetailsModel->getItemsByOrderIdAndUserId($orderId, $userId);

        if(count($items) === 0)
            return JsonAPIResponse::sendErrorResponse("No items found");

        $response = [
            'orderDetails' => $order,
            'items' => $items
        ];

        return JsonAPIResponse::sendSuccessResponse('Order specific to a Sales Representative.', $response);
    }

}
