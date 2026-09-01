<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\GemelliGarage\Service;
class ServiceController extends Controller { public function index() { return Service::paginate(); } }