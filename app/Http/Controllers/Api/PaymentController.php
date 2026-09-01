<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\GemelliGarage\Payment;
class PaymentController extends Controller { public function index() { return Payment::paginate(); } }