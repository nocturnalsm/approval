<?php

namespace NocturnalSm\Approval\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use NocturnalSm\Approval\Entities\Approval;
use NocturnalSm\Approval\Entities\ApprovalResponse;

class ApprovalController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('approval::index');
    }
    public function responses($id)
    {
        $approval = Approval::find($id);
        $responses = $approval->responses();
        return response()->json($responses->get());
    }
    public function approve($id)
    {   
        $approval = Approval::find($id);
        $user = Auth::user();
        $approval->user()->associate($approval);
        $user->response = Approval::STATUS_APPROVED;
        $user->save();
        $approval->status = 'approved';
        $approval->save();
    }
    public function reject($id)
    {

    }
    
}
