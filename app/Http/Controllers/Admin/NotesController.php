<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class NotesController extends Controller
{
    public function index()
    {
        return view('pages.admin.notes.index');
    }
}
