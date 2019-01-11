<?php

class Product
{

    // Función para obtener el stock bloqueado por pedidos en curso Con o sin caché
    public static function getOrdersQuantity($productId, $cache = false, $cacheDuration = 60){
        if ($cache) {
            $ordersQuantity = OrderLine::getDb()->cache(function ($db) use ($productId) {
                return OrderLine::find()->select('SUM(quantity) as quantity')->joinWith('order')->where("(order.status = '" . Order::STATUS_PENDING . "' OR order.status = '" . Order::STATUS_PROCESSING . "' OR order.status = '" . Order::STATUS_WAITING_ACCEPTANCE . "') AND order_line.product_id = $productId")->scalar();
            }, $cacheDuration);

        } else {
            $ordersQuantity = OrderLine::find()->select('SUM(quantity) as quantity')->joinWith('order')->where("(order.status = '" . Order::STATUS_PENDING . "' OR order.status = '" . Order::STATUS_PROCESSING . "' OR order.status = '" . Order::STATUS_WAITING_ACCEPTANCE . "') AND order_line.product_id = $productId")->scalar();
        }
        return $ordersQuantity ? $ordersQuantity :0;
    }



    // Función para obtener el stock bloqueado Con o sin caché
    public static function getBlockedStockQuantity($productId, $cache = false, $cacheDuration = 60 ){
        if ($cache) {

            $blockedStockQuantity = BlockedStock::getDb()->cache(function ($db) use ($productId) {
                return BlockedStock::find()->select('SUM(quantity) as quantity')->joinWith('shoppingCart')->where("blocked_stock.product_id = $productId AND blocked_stock_date > '" . date('Y-m-d H:i:s') . "' AND (shopping_cart_id IS NULL OR shopping_cart.status = '" . ShoppingCart::STATUS_PENDING . "')")->scalar();
            }, $cacheDuration);
        } else {
            $blockedStockQuantity = BlockedStock::find()->select('SUM(quantity) as quantity')->joinWith('shoppingCart')->where("blocked_stock.product_id = $productId AND blocked_stock_to_date > '" . date('Y-m-d H:i:s') . "' AND (shopping_cart_id IS NULL OR shopping_cart.status = '" . ShoppingCart::STATUS_PENDING . "')")->scalar();
        }
        return $blockedStockQuantity ? $blockedStockQuantity :0;
    }




    public static function stock($productId, $quantityAvailable, $cache = false, $cacheDuration = 60, $securityStockConfig = null) {
        // Obtenemos el stock bloqueado por pedidos en curso
        $ordersQuantity = self::getOrdersQuantity($productId,$cache,$cacheDuration);
        // Obtenemos el stock bloqueado
        $blockedStockQuantity = self::getBlockedStockQuantity($productId,$cache,$cacheDuration);
        //Obtenemos La Cantidad disponible
        $quantity = $quantityAvailable - $ordersQuantity - $blockedStockQuantity;

        if ($quantityAvailable >= 0) {
            if (!empty($securityStockConfig)) {
                $quantity = ShopChannel::applySecurityStockConfig(
                    $quantity,
                    @$securityStockConfig->mode,
                    @$securityStockConfig->quantity
                );
            }
            return $quantity > 0 ? $quantity : 0;
        }else{
            return $quantityAvailable;
        }
        return 0;
    }
}

