<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\BerberApp\DeviceToken;
class DeviceTokenController extends Controller { public function index() { return DeviceToken::paginate(); } }