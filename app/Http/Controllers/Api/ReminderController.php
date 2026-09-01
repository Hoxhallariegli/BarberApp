<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\BerberApp\Reminder;
class ReminderController extends Controller { public function index() { return Reminder::paginate(); } }