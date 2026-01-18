<?php
namespace App\Http\Controllers\Api;

use App\Models\Make;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MakeController extends Controller {
    public function index() {
        return Make::all(['id', 'name', ]);
        //return response()->json(['makes' => 'test']);
    }
}
