<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\ECommerce\Customer;
class CustomerController extends Controller { public function index() { return Customer::paginate(); } }