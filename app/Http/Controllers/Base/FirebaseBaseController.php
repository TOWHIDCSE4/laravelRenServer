<?php
 namespace App\Http\Controllers\Base;
 use Kreait\Firebase\Factory;
use App\Services\FirebaseService;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

 class FirebaseBaseController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    protected $firebase;
    protected $database;
    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebase = $firebaseService->firebase;
        $this->database = $this->firebase->getDatabase();
    }
}