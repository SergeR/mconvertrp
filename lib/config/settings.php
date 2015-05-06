<?php
return array(
    'boolean_feature_id' => array(
        'title'            => 'Характеристика товара',
        'description'      => 'Характеристика типа Boolean (да/нет) определяющая возможность отправки продукта Мультиконвертом',
        'control_type'     => waHtmlControl::SELECT,
        'options_callback' => array('mconvertrpShipping', 'listBooleanFeatures'),
        'value'            => 0
    ),
    'max_weight'         => array(
        'title'        => 'Максимальный вес',
        'description'  => 'Максимальный вес заказа в граммах',
        'control_type' => waHtmlControl::INPUT,
        'value'        => 150
    ),
    'fixed_price'        => array(
        'title'        => 'Стоимость доставки',
        'description'  => 'Стоимость доставки этим способом',
        'control_type' => waHtmlControl::INPUT,
        'value'        => 150
    )
);
