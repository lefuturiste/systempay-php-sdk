<?php

namespace App;

/**
 * Documentation EN https://paiement.systempay.fr/doc/en-EN/form-payment/quick-start-guide/tla1427193445290.pdf
 * Documentation FR https://www.ocl.natixis.com/systempay/public/uploads/fichier/Guide_d%27implementation_formulaire_Paiement20122013163915.pdf
 *
 * Class SystemPay
 * @package App
 */
class SystemPay
{

    private const PAYMENT_URL = "https://systempay.cyberpluspaiement.com/vads-payment/";

    public const CURRENCY_AUD = '036';
    public const CURRENCY_CAD = '124';
    public const CURRENCY_CNY = '156';
    public const CURRENCY_DKK = '208';
    public const CURRENCY_JPY = '392';
    public const CURRENCY_NOK = '578';
    public const CURRENCY_SEK = '752';
    public const CURRENCY_CHF = '756';
    public const CURRENCY_GBP = '826';
    public const CURRENCY_USD = '840';
    public const CURRENCY_XPF = '953';
    public const CURRENCY_EUR = '978';

    /**
     * @var Transaction
     */
    private $transaction;

    private $siteId;
    private $testKey;
    private $productionKey;
    private $isProductionMode = false;
    private $returnUrl;
    private $actionMode = "INTERACTIVE";
    private $pageAction = "PAYMENT";
    private $paymentConfig = "SINGLE";
    private $version = "V2";
    private $redirectSuccessMessage = "Redirection...";
    private $redirectErrorMessage = "Redirection...";

    public function setSiteId($siteId)
    {
        $this->siteId = $siteId;
        return $this;
    }

    public function setTestKey($testKey)
    {
        $this->testKey = $testKey;
        return $this;
    }

    public function setProductionKey($productionKey)
    {
        $this->productionKey = $productionKey;
        return $this;
    }

    public function setProductionMode(bool $productionMode)
    {
        $this->isProductionMode = $productionMode;
        return $this;
    }

    public function setReturnUrl(string $returnUrl)
    {
        $this->returnUrl = $returnUrl;
    }

    public function createTransaction(int $amount = 1000, int $currency = 978)
    {
        $this->transaction = new Transaction();
        $this->transaction->generateId();
        $this->transaction->generateDate();
        $this->transaction->currency = $currency;
        $this->transaction->amount = $amount;

        return $this;
    }

    public function getPaymentUrl()
    {
        return self::PAYMENT_URL;
    }

    public function getFields()
    {
        $fields = [
            'action_mode' => $this->actionMode,
            'ctx_mode' => $this->isProductionMode ? 'PRODUCTION' : 'TEST',
            'page_action' => $this->pageAction,
            'payment_config' => $this->paymentConfig,
            'site_id' => $this->siteId,
            'version' => $this->version,
            'redirect_success_message' => $this->redirectSuccessMessage,
            'redirect_error_message' => $this->redirectErrorMessage,
            'url_return' => $this->returnUrl,
            'amount' => $this->transaction->amount,
            'currency' => $this->transaction->currency,
            'trans_id' => $this->transaction->id,
            'trans_date' => $this->transaction->date
        ];
        $fields = $this->mapPrefix($fields);
        $fields['signature'] = ($this->generateSignature($fields));
        return $fields;
    }

    public function getHtmlFields()
    {
        $fields = $this->getFields();
        $html = "";
        foreach ($fields as $key => $value) {
            $html .= "\r\n" . "<input type='hidden' name='$key' value='" . $value . "' />";
        }
        return $html;
    }

    public function generateSignature($fields)
    {
        $signature = "";
        ksort($fields);
        foreach ($fields as $field => $value)
            $signature .= $value . "+";
        $signature .= $this->getCurrentShopKey();
        $signature = base64_encode(hash_hmac('sha256', $signature, $this->getCurrentShopKey(), true));
        return $signature;
    }

    /**
     * @param array $fields
     * @return array
     */
    private function mapPrefix(array $fields)
    {
        $result = [];
        foreach ($fields as $field => $value) {
            $result[sprintf('vads_%s', $field)] = $value;
        }
        return $result;
    }

    public function getCurrentShopKey()
    {
        return $this->isProductionMode ? $this->productionKey : $this->testKey;
    }

    public function processReturn($fields)
    {
        /**
        {vads_amount:'1500',
        vads_auth_mode:'FULL',
        vads_auth_number:'3fe6f6',
        vads_auth_result:'00',
        vads_capture_delay:'0',
        vads_card_brand:'CB',
        vads_card_number:'497010XXXXXX
        vads_payment_certificate:'cae0
        33da5db54b18e',
        vads_ctx_mode:'TEST',
        vads_currency:'978',
        vads_effective_amount:'1500',
        vads_effective_currency:'978',
        vads_site_id:'18058478',
        vads_trans_date:'2019041914480
        vads_trans_id:'262401',
        vads_trans_uuid:'2a53d46fb50e4
        vads_validation_mode:'0',
        vads_version:'V2',
        vads_warranty_result:'YES',
        vads_payment_src:'EC',
        vads_sequence_number:'1',
        vads_contract_used:'8933264',
        vads_threeds_cavv:'Q2F2dkNhdnZ
        vads_threeds_eci:'05',
        vads_threeds_xid:'MFp2enNPcU8z
        vads_threeds_cavvAlgorithm:'2'
        vads_threeds_status:'Y',
        vads_threeds_sign_valid:'1',
        vads_threeds_error_code:'',
        vads_threeds_exit_status:'10',
        vads_result:'00',
        vads_extra_result:'',
        vads_card_country:'FR',
        vads_language:'en',
        vads_brand_management:'{"userC
        ":"CB|VISA","brand":"CB"}',
        vads_hash:
        'ca0b8a7dc0e9c0be9dd63ac235497
        63fcdb1eb27f',
        vads_url_check_src:'PAY',
        vads_action_mode:'INTERACTIVE'
        vads_payment_config:'SINGLE',
        vads_page_action:'PAYMENT',
        signature:'Vf7zRX/ZYLjqXtk1MGxk='}
         */
        $providedSignature = $fields['signature'];
        unset($fields['signature']);
        $computedSignature = $this->generateSignature($fields);
        $isValidSignature = $providedSignature === $computedSignature;
        ob_start();
        var_dump($fields);
        var_dump($isValidSignature);
        var_dump($computedSignature);
        error_log(ob_get_clean(), 4);
    }
}
