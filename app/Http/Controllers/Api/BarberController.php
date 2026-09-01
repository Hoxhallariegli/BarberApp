<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\BerberApp\Barber;
class BarberController extends Controller { public function index() { return Barber::paginate(); } }