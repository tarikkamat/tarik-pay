<?php

namespace Iyzico\IyzipayWoocommerce\Common\Helpers;

use Iyzico\IyzipayWoocommerce\Checkout\CheckoutSettings;

class BuyerProtection
{
    public static function iyzicoOverlayScriptMobileCss()
    {
        echo '<style>
	                @media screen and (max-width: 380px) {
                        ._1xrVL7npYN5CKybp32heXk {
		                    position: fixed;
			                bottom: 0!important;
    		                top: unset;
    		                left: 0;
    		                width: 100%;
                        }
                    }
	            </style>';
    }

    public function getOverlayScript()
    {
        $checkoutSettings = new CheckoutSettings();
        $token = get_option('iyzico_overlay_token');
        $position = $checkoutSettings->findByKey('overlay_script');
        $overlayScript = false;

        if ($position === 'bottomLeft' || $position === 'bottomRight') {
            $overlayScript = "<script> window.iyz = { token:'" . $token . "', position:'" . $position . "',ideaSoft: false, pwi:true};</script>
                    <script src='https://static.iyzipay.com/buyer-protection/buyer-protection.js' type='text/javascript'></script>";
        }

        echo $overlayScript;
    }
}