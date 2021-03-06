<?php
/**
 * WORK IN PROGRESS EXAMPLE
 */

use Lefuturiste\SystemPay\SystemPay;

require '../vendor/autoload.php';

$systemPay = new SystemPay();
$systemPay->setSiteId('XXX');
$systemPay->setProductionMode(false);
$systemPay->setTestKey('XXX'); // or certificate
$systemPay->setReturnUrl('http://example.com/return');
$systemPay->setShopUrl('http://lefuturiste.fr');
$systemPay->setLanguage('fr');
$systemPay->setReturnMode('GET');

if ($_SERVER['REQUEST_URI'] === '/') {
    // create a payment
    $systemPay->createTransaction(15 * 100); // here we create a transaction of 15 euros = 15 * 100 = 15000 cents

    $paymentUrl = $systemPay->getPaymentUrl(); // return the form url where to submit the fields (action attribute on html)

    $systemPay->getFields(); // return a array of fields

    $htmlFields = $systemPay->getHtmlFields(); // return a string of the html that correspond to the form fields

    ?>
    <form action="<?= $paymentUrl ?>" method="post">
        <?= $htmlFields ?>
        <button type="submit">Pay</button>
    </form>
    <?php
}

if ($_SERVER['REQUEST_URI'] === '/return') {
    // this is where the user is redirected after the payment
    echo "Welcome back! Thanks for your order!";
}

if ($_SERVER['REQUEST_URI'] === '/notification') {
    // process the payment notification (request from systempay)
    $systemPay->processReturn($_POST);

}
