<?php

function sg_get_currency_code_exponent ($currency = 'USD')
{
    $array = array(
            'AED' => 2,
            'ARS' => 2,
            'AUD' => 2,
            'BDT' => 2,
            'BGN' => 2,
            'BRL' => 2,
            'CAD' => 2,
            'CHF' => 2,
            'CLP' => 0,
            'CNY' => 2,
            'COP' => 2,
            'CZK' => 2,
            'DKK' => 2,
            'DOP' => 2,
            'EGP' => 2,
            'EUR' => 2,
            'GBP' => 2,
            'HKD' => 2,
            'HRK' => 2,
            'HUF' => 2,
            'IDR' => 2,
            'ILS' => 2,
            'INR' => 2,
            'ISK' => 0,
            'JPY' => 0,
            'KES' => 2,
            'LAK' => 2,
            'KRW' => 0,
            'MXN' => 2,
            'MYR' => 2,
            'NGN' => 2,
            'NOK' => 2,
            'NPR' => 2,
            'NZD' => 2,
            'PHP' => 2,
            'PKR' => 2,
            'PLN' => 2,
            'PYG' => 0,
            'RON' => 2,
            'RUB' => 2,
            'SEK' => 2,
            'SGD' => 2,
            'THB' => 2,
            'TRY' => 2,
            'TWD' => 2,
            'UAH' => 2,
            'USD' => 2,
            'VND' => 0,
            'ZAR' => 2
    );
    return $array[$currency];
}

/**
 * Helper function that returns the index of the array where the payment method
 * id matches
 * the provided id.
 *
 * @param array $methods            
 * @param string $id            
 * @return mixed|NULL
 */
function sg_get_index_of_payment_method ($methods, $id)
{
    foreach ($methods as $index => $method) {
        if ($method['id'] === $id) {
            return $index;
        }
    }
    return null;
}

/**
 * Return an array of currencies.
 *
 * @return mixed
 */
function sg_get_currency_codes ()
{
    return apply_filters('stripe_gateway_currencies', 
            array(
                    'AED' => __('United Arab Emirates Dirham', 'woocommerce'),
                    'ARS' => __('Argentine Peso', 'woocommerce'),
                    'AUD' => __('Australian Dollars', 'woocommerce'),
                    'BDT' => __('Bangladeshi Taka', 'woocommerce'),
                    'BGN' => __('Bulgarian Lev', 'woocommerce'),
                    'BRL' => __('Brazilian Real', 'woocommerce'),
                    'CAD' => __('Canadian Dollars', 'woocommerce'),
                    'CHF' => __('Swiss Franc', 'woocommerce'),
                    'CLP' => __('Chilean Peso', 'woocommerce'),
                    'CNY' => __('Chinese Yuan', 'woocommerce'),
                    'COP' => __('Colombian Peso', 'woocommerce'),
                    'CZK' => __('Czech Koruna', 'woocommerce'),
                    'DKK' => __('Danish Krone', 'woocommerce'),
                    'DOP' => __('Dominican Peso', 'woocommerce'),
                    'EGP' => __('Egyptian Pound', 'woocommerce'),
                    'EUR' => __('Euros', 'woocommerce'),
                    'GBP' => __('Pounds Sterling', 'woocommerce'),
                    'HKD' => __('Hong Kong Dollar', 'woocommerce'),
                    'HRK' => __('Croatia kuna', 'woocommerce'),
                    'HUF' => __('Hungarian Forint', 'woocommerce'),
                    'IDR' => __('Indonesia Rupiah', 'woocommerce'),
                    'ILS' => __('Israeli Shekel', 'woocommerce'),
                    'INR' => __('Indian Rupee', 'woocommerce'),
                    'ISK' => __('Icelandic krona', 'woocommerce'),
                    'JPY' => __('Japanese Yen', 'woocommerce'),
                    'KES' => __('Kenyan shilling', 'woocommerce'),
                    'LAK' => __('Lao Kip', 'woocommerce'),
                    'KRW' => __('South Korean Won', 'woocommerce'),
                    'MXN' => __('Mexican Peso', 'woocommerce'),
                    'MYR' => __('Malaysian Ringgits', 'woocommerce'),
                    'NGN' => __('Nigerian Naira', 'woocommerce'),
                    'NOK' => __('Norwegian Krone', 'woocommerce'),
                    'NPR' => __('Nepali Rupee', 'woocommerce'),
                    'NZD' => __('New Zealand Dollar', 'woocommerce'),
                    'PHP' => __('Philippine Pesos', 'woocommerce'),
                    'PKR' => __('Pakistani Rupee', 'woocommerce'),
                    'PLN' => __('Polish Zloty', 'woocommerce'),
                    'PYG' => __('Paraguayan Guaraní', 'woocommerce'),
                    'RON' => __('Romanian Leu', 'woocommerce'),
                    'RUB' => __('Russian Ruble', 'woocommerce'),
                    'SEK' => __('Swedish Krona', 'woocommerce'),
                    'SGD' => __('Singapore Dollar', 'woocommerce'),
                    'THB' => __('Thai Baht', 'woocommerce'),
                    'TRY' => __('Turkish Lira', 'woocommerce'),
                    'TWD' => __('Taiwan New Dollars', 'woocommerce'),
                    'UAH' => __('Ukrainian Hryvnia', 'woocommerce'),
                    'USD' => __('US Dollars', 'woocommerce'),
                    'VND' => __('Vietnamese Dong', 'woocommerce'),
                    'ZAR' => __('South African rand', 'woocommerce')
            ));
}

/**
 * Return the html symbol for the provided currency.
 *
 * @return string[]
 */
function sg_get_currency_symbol ($symbol = 'USD')
{
    $symbols = array(
            'AED' => '&#1583;.&#1573;', // ?
            'AFN' => '&#65;&#102;',
            'ALL' => '&#76;&#101;&#107;',
            'AMD' => '',
            'ANG' => '&#402;',
            'AOA' => '&#75;&#122;', // ?
            'ARS' => '&#36;',
            'AUD' => '&#36;',
            'AWG' => '&#402;',
            'AZN' => '&#1084;&#1072;&#1085;',
            'BAM' => '&#75;&#77;',
            'BBD' => '&#36;',
            'BDT' => '&#2547;', // ?
            'BGN' => '&#1083;&#1074;',
            'BHD' => '.&#1583;.&#1576;', // ?
            'BIF' => '&#70;&#66;&#117;', // ?
            'BMD' => '&#36;',
            'BND' => '&#36;',
            'BOB' => '&#36;&#98;',
            'BRL' => '&#82;&#36;',
            'BSD' => '&#36;',
            'BTN' => '&#78;&#117;&#46;', // ?
            'BWP' => '&#80;',
            'BYR' => '&#112;&#46;',
            'BZD' => '&#66;&#90;&#36;',
            'CAD' => '&#36;',
            'CDF' => '&#70;&#67;',
            'CHF' => '&#67;&#72;&#70;',
            'CLF' => '', // ?
            'CLP' => '&#36;',
            'CNY' => '&#165;',
            'COP' => '&#36;',
            'CRC' => '&#8353;',
            'CUP' => '&#8396;',
            'CVE' => '&#36;', // ?
            'CZK' => '&#75;&#269;',
            'DJF' => '&#70;&#100;&#106;', // ?
            'DKK' => '&#107;&#114;',
            'DOP' => '&#82;&#68;&#36;',
            'DZD' => '&#1583;&#1580;', // ?
            'EGP' => '&#163;',
            'ETB' => '&#66;&#114;',
            'EUR' => '&#8364;',
            'FJD' => '&#36;',
            'FKP' => '&#163;',
            'GBP' => '&#163;',
            'GEL' => '&#4314;', // ?
            'GHS' => '&#162;',
            'GIP' => '&#163;',
            'GMD' => '&#68;', // ?
            'GNF' => '&#70;&#71;', // ?
            'GTQ' => '&#81;',
            'GYD' => '&#36;',
            'HKD' => '&#36;',
            'HNL' => '&#76;',
            'HRK' => '&#107;&#110;',
            'HTG' => '&#71;', // ?
            'HUF' => '&#70;&#116;',
            'IDR' => '&#82;&#112;',
            'ILS' => '&#8362;',
            'INR' => '&#8377;',
            'IQD' => '&#1593;.&#1583;', // ?
            'IRR' => '&#65020;',
            'ISK' => '&#107;&#114;',
            'JEP' => '&#163;',
            'JMD' => '&#74;&#36;',
            'JOD' => '&#74;&#68;', // ?
            'JPY' => '&#165;',
            'KES' => '&#75;&#83;&#104;', // ?
            'KGS' => '&#1083;&#1074;',
            'KHR' => '&#6107;',
            'KMF' => '&#67;&#70;', // ?
            'KPW' => '&#8361;',
            'KRW' => '&#8361;',
            'KWD' => '&#1583;.&#1603;', // ?
            'KYD' => '&#36;',
            'KZT' => '&#1083;&#1074;',
            'LAK' => '&#8365;',
            'LBP' => '&#163;',
            'LKR' => '&#8360;',
            'LRD' => '&#36;',
            'LSL' => '&#76;', // ?
            'LTL' => '&#76;&#116;',
            'LVL' => '&#76;&#115;',
            'LYD' => '&#1604;.&#1583;', // ?
            'MAD' => '&#1583;.&#1605;.', // ?
            'MDL' => '&#76;',
            'MGA' => '&#65;&#114;', // ?
            'MKD' => '&#1076;&#1077;&#1085;',
            'MMK' => '&#75;',
            'MNT' => '&#8366;',
            'MOP' => '&#77;&#79;&#80;&#36;', // ?
            'MRO' => '&#85;&#77;', // ?
            'MUR' => '&#8360;', // ?
            'MVR' => '.&#1923;', // ?
            'MWK' => '&#77;&#75;',
            'MXN' => '&#36;',
            'MYR' => '&#82;&#77;',
            'MZN' => '&#77;&#84;',
            'NAD' => '&#36;',
            'NGN' => '&#8358;',
            'NIO' => '&#67;&#36;',
            'NOK' => '&#107;&#114;',
            'NPR' => '&#8360;',
            'NZD' => '&#36;',
            'OMR' => '&#65020;',
            'PAB' => '&#66;&#47;&#46;',
            'PEN' => '&#83;&#47;&#46;',
            'PGK' => '&#75;', // ?
            'PHP' => '&#8369;',
            'PKR' => '&#8360;',
            'PLN' => '&#122;&#322;',
            'PYG' => '&#71;&#115;',
            'QAR' => '&#65020;',
            'RON' => '&#108;&#101;&#105;',
            'RSD' => '&#1044;&#1080;&#1085;&#46;',
            'RUB' => '&#1088;&#1091;&#1073;',
            'RWF' => '&#1585;.&#1587;',
            'SAR' => '&#65020;',
            'SBD' => '&#36;',
            'SCR' => '&#8360;',
            'SDG' => '&#163;', // ?
            'SEK' => '&#107;&#114;',
            'SGD' => '&#36;',
            'SHP' => '&#163;',
            'SLL' => '&#76;&#101;', // ?
            'SOS' => '&#83;',
            'SRD' => '&#36;',
            'STD' => '&#68;&#98;', // ?
            'SVC' => '&#36;',
            'SYP' => '&#163;',
            'SZL' => '&#76;', // ?
            'THB' => '&#3647;',
            'TJS' => '&#84;&#74;&#83;', // ? TJS (guess)
            'TMT' => '&#109;',
            'TND' => '&#1583;.&#1578;',
            'TOP' => '&#84;&#36;',
            'TRY' => '&#8356;', // New Turkey Lira (old symbol used)
            'TTD' => '&#36;',
            'TWD' => '&#78;&#84;&#36;',
            'TZS' => '',
            'UAH' => '&#8372;',
            'UGX' => '&#85;&#83;&#104;',
            'USD' => '&#36;',
            'UYU' => '&#36;&#85;',
            'UZS' => '&#1083;&#1074;',
            'VEF' => '&#66;&#115;',
            'VND' => '&#8363;',
            'VUV' => '&#86;&#84;',
            'WST' => '&#87;&#83;&#36;',
            'XAF' => '&#70;&#67;&#70;&#65;',
            'XCD' => '&#36;',
            'XDR' => '',
            'XOF' => '',
            'XPF' => '&#70;',
            'YER' => '&#65020;',
            'ZAR' => '&#82;',
            'ZMK' => '&#90;&#75;', // ?
            'ZWL' => '&#90;&#36;'
    );
    return isset($symbols[$symbol]) ? $symbols[$symbol] : '';
}

/**
 * Function that generates an attributes array and implodes it for use in an
 * html field.
 *
 * @param array $attributes            
 * @param string $echo            
 */
function sg_get_html_field_attributes ($attributes, $echo = false)
{
    $attrs = array();
    foreach ($attributes as $k => $v) {
        $attrs[] = $k . '="' . $v . '"';
    }
    if($echo){
        echo implode(' ', $attrs);
    }else{
        return implode(' ', $attrs);
    }
}
