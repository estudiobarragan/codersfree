<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;
use PhpParser\Node\Stmt\TryCatch;

/* require __DIR__  . '/vendor/autoload.php'; */

class PaymentController extends Controller
{
  private $apiContext;

  public function __construct()
  {

    // After Step 1
    $this->apiContext = new ApiContext(
      new \PayPal\Auth\OAuthTokenCredential(
        config('paypal.paypal.client_id'),     // ClientID
        config('paypal.paypal.client_secret')      // ClientSecret
      )
    );
  }

  public function checkout(Course $course)
  {
    return view('payment.checkout', compact('course'));
  }
  public function pay(Course $course)
  {

    // After Step 2
    $payer = new Payer();
    $payer->setPaymentMethod('paypal');

    $amount = new Amount();
    $amount->setTotal($course->price->value);
    $amount->setCurrency('USD');

    $transaction = new Transaction();
    $transaction->setAmount($amount);
    // $transaction->setDescription('Estas por comprar el curso: ' . $course->title);

    $redirectUrls = new RedirectUrls();
    $redirectUrls->setReturnUrl(route('payment.approved', $course))
      ->setCancelUrl(route('payment.checkout', $course));

    $payment = new Payment();
    $payment->setIntent('sale')
      ->setPayer($payer)
      ->setTransactions(array($transaction))
      ->setRedirectUrls($redirectUrls);

    // After Step 3
    try {
      $payment->create($this->apiContext);

      return redirect()->away($payment->getApprovalLink());
    } catch (PayPalConnectionException $ex) {
      // This will print the detailed information on the exception.
      //REALLY HELPFUL FOR DEBUGGING
      echo $ex->getData();
    }
  }

  public function approved(Request $request, Course $course)
  {

    $paymentId = $_GET['paymentId'];


    try {
      $payment = Payment::get($paymentId, $this->apiContext);
    } catch (Exception $ex) {

      dd($ex);
      return redirect()->route('payment.checkout', $course)->with('failed', 'Su pago no se concreto, por favor, vuelva a intentarlo');
    }


    $execution = new PaymentExecution();
    $execution->setPayerId($_GET['PayerID']);

    try {
      $result = $payment->execute($execution, $this->apiContext);
    } catch (Exception $ex) {
      return redirect()->route('payment.checkout', $course)->with('failed', 'Su pago no se concreto, por favor, vuelva a intentarlo');
    }

    if ($result->getState() === 'approved') {
      $course->students()->attach(auth()->user()->id);
      return redirect()->route('courses.status', $course)->with('approved', 'Gracias por su compra, el curso queda habilitado.');
    } else {
      return redirect()->route('payment.checkout', $course)->with('failed', 'Su pago no se concreto, por favor, vuelva a intentarlo');
    }
  }
}
