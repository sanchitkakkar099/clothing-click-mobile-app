<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\contact_leads;
use Config;

class ContactLeadController extends SystemAdminController
{
    var $breadcum = ['icon'=>'mdi mdi-phone','breadcum'=>['Contact Leads']];

    public function __construct()
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $filter = $request->all();
        $leads = contact_leads::where(function($q)use($filter){
                    if(!empty($filter['search'])){
                        $q->where('name','LIKE','%'.$filter['search'].'%')
                        ->orwhere('email','LIKE',$filter['search'])
                        ->orwhere('phone','LIKE',$filter['search'])
                        ->orwhere('subject','LIKE','%'.$filter['search'].'%');
                    }
                })
                ->orderby('created_at','desc')->paginate(Config::get('constants.pageRecords'));
        $breadcum = $this->breadcum;
        return view('admin.contact_lead.list',compact('leads','breadcum','filter'));
    }

    public function delete($id)
    {
        contact_leads::destroy($id);
        return redirect()->back()->with('success','Contact lead deleted successfully.');
    }
}
