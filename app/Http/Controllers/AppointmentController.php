<?php

namespace App\Http\Controllers;

use App\Models\Appointment;

class AppointmentController extends Controller
{
    public function index()
    {
        return response()->json(Appointment::all(), 200, [], JSON_PRETTY_PRINT);
    }
}
