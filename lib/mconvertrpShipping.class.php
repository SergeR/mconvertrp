<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 1.0.0
 * @copyright (c) 2015, Serge Rodovnichenko
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */

/**
 * @package mconvertrp
 * @property int boolean_feature_id
 * @property int max_weight
 * @property int fixed_price
 */
class mconvertrpShipping extends waShipping
{

    /**
     *  Все в рублях
     *
     * @return string ISO3 currency code or array of ISO3 codes
     */
    public function allowedCurrency()
    {
        return 'RUB';
    }

    /**
     *
     * @return string Weight units or array of weight units
     */
    public function allowedWeightUnit()
    {
        return 'g';
    }

    /**
     * Ограничение только по стране (Россия)
     *
     * @return array
     */
    public function allowedAddress()
    {
        return array(
            array(
                'country' => 'rus'
            )
        );
    }

    /**
     * Расчет стоимости доставки здесь
     *
     * @todo Сделать настройку того, что выводить при ошибке. Если вернуть false плагин вообще не будет в списке, если строку, он будет просто запрещен для выбора
     */
    protected function calculate()
    {
        $weight = $this->getTotalWeight();
        $result = FALSE;

        if ($weight > $this->max_weight) {
//            $result = "Общий вес заказа превышает максимально допустимый";
            $result = FALSE;
        } elseif ($this->allowedProductsInCart()) {
            $result = array(
                'delivery' => array(
                    'currency' => 'RUB',
                    'rate'     => $this->fixed_price
                )
            );
        }

        return $result;
    }

    /**
     * Проверяет корзину на наличие "запрещенных товаров".
     * Если приложение 'Магазин' не установлено, запрещает отправку этим способом
     *
     * Запрещенные товары это такие, у которых либо не задана опция (44) либо она равна 0
     *
     * @todo Выбрать какое-то действие, если корзина пуста. Нужно для совместимости с плагинами расчета доставки на карточке товара - они же считают не по корзине
     *
     * @return bool
     */
    protected function allowedProductsInCart()
    {
        try {
            waSystem::getInstance('shop');

        } catch (waException $e) {
            return FALSE;
        }

        $cart = new shopCart();
        $cartItems = $cart->items();

        $allowed_products = array();
        foreach ($cartItems as $i) {
            $allowed_products[$i['product_id']] = 0;
        }

        $BooleanFeature = shopFeatureModel::getValuesModel(shopFeatureModel::TYPE_BOOLEAN);

        // @todo Remove direct SELECT when bug in Shopscript will be fixed
        $sql = "SELECT pf.product_id, pf.feature_value_id  value FROM shop_product_features pf
                WHERE pf.product_id IN (i:0) AND pf.feature_id = i:1";

        $allowed_products = $BooleanFeature->query($sql, array_keys($allowed_products), $this->boolean_feature_id)
                ->fetchAll('product_id', TRUE) + $allowed_products;

        foreach ($allowed_products as $shippingAllowed) {
            if (!$shippingAllowed) {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * Возвращает массив полей формы запроса адреса доставки, которые должны запрашиваться у покупателя во время оформления заказа.
     *
     * @see waShipping::requestedAddressFields()
     * @return array
     */
    public function requestedAddressFields()
    {
        return array(
            'zip'     => array(),
            'country' => array('hidden' => TRUE, 'value' => 'rus', 'cost' => TRUE),
            'region'  => array(),
            'city'    => array(),
            'street'  => array(),
        );
    }

    /**
     * Возвращает список характеристик типа boolean. Вызывается из настроек плагина
     *
     * @return array
     * @throws waException
     */
    public static function listBooleanFeatures()
    {

        waSystem::getInstance('shop');
        $features = array(0 => "Выберите характеристику");

        $Feature = new shopFeatureModel();

        $features += $Feature->select('id,name')
            ->where("parent_id IS NULL AND status='public' AND type='boolean'")
            ->order('name')
            ->fetchAll('id', TRUE);

        return $features;
    }
}