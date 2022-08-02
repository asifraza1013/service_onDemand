<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Notifications\TicketNotificationSeller;
use App\Review;
use DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\OrderAdditional;
use App\OrderInclude;
use App\ServiceCity;
use App\ServiceArea;
use App\Country;
use App\Order;
use App\User;
use Auth;
use App\SupportTicket;
use App\SupportDepartment;
use App\SupportTicketMessage;
use App\Events\SupportMessage;
use App\Helpers\FlashMsg;
use Carbon\Carbon;

class BuyerController extends Controller
{
     public function __construct(){
        $this->middleware('inactiveuser');
    }
    
    public function buyerDashboard()
    {
        // toastr_success('This feature is under development. Will be available soon.');
        // return redirect('/');
        $buyer_id = Auth::guard('web')->user()->id;

        $pending_order = Order::where(['buyer_id'=>$buyer_id, 'status'=>0])->count();
        $active_order = Order::where(['buyer_id'=>$buyer_id, 'status'=>1])->count();
        $complete_order = Order::where(['buyer_id'=>$buyer_id, 'status'=>2])->count();
        $total_order = Order::where('buyer_id',$buyer_id)->count();
        $last_10_order = Order::where('buyer_id',$buyer_id)->take(10)->latest()->get();
        $last_10_tickets = SupportTicket::where('buyer_id',$buyer_id)->take(10)->latest()->get();

        $currentOrder = null;
        $totalDuration = null;
        $durationInMinuts = null;
        if(count($last_10_order) && $last_10_order[0]->service_type == 2){
            $currentOrder = $last_10_order[0];
            if($currentOrder->status == 0 && $currentOrder->payment_status == 'complete'){
                $currentOrder = $last_10_order[0];
                $startTime = Carbon::parse($currentOrder->created_at);
                $endTime = Carbon::parse(now());
                $durationInMinuts = $endTime->diffInMinutes($startTime);
                if($durationInMinuts < 45){
                    $totalDuration = $endTime->diffInSeconds($startTime);
                    $totalDuration = gmdate('i:s', $totalDuration);
                }
            }else{
                $currentOrder = null;
            }
        }
        return view('frontend.user.buyer.dashboard.dashboard',compact([
            'pending_order',
            'active_order',
            'complete_order',
            'total_order',
            'last_10_order',
            'last_10_tickets',
            'currentOrder',
            'totalDuration',
            'durationInMinuts',
        ]));
    }

    public function orderStatus(Request $request,$id=null)
    {
        $payment_status = Order::select('id','payment_status','status','email','name')->where('id',$request->order_id)->first();
        if($payment_status->status !=2){
            if($payment_status->payment_status =='complete'){

                //get old status
                if($payment_status->status ==0){
                    $old_status = 'Pending';
                }elseif($payment_status->status==1){
                    $old_status = 'Active';
                }elseif($payment_status->status==3){
                    $old_status = 'Delivered';
                }elseif($payment_status->status==4){
                    $old_status = 'Cancelled';
                }

                Order::where('id',$request->order_id)->update(['status'=>$request->status]);

                //get new status
                if($request->status==1){
                    $new_status = 'Active';
                }elseif($request->status==4){
                    $new_status = 'Cancelled';
                }

                toastr_success(__('Status Change Success---'));
                return redirect()->back();
            }else{
                toastr_error(__('You can not change order status due to payment status pending'));
                return redirect()->back();
            }
        }else{
            toastr_error(__('You can not change order status because this order already completed.'));
            return redirect()->back();
        }
        
    }

    public function buyerOrders()
    {
        $orders = Order::with('online_order_ticket')
        ->where('buyer_id', Auth::guard('web')->user()->id)
        ->where('payment_status', '!=','')
        ->latest()
        ->paginate(10);
        return view('frontend.user.buyer.order.orders', compact('orders'));
    }

    public function orderDetails($id=null)
    {
        $order_details = Order::with('seller')
        ->where('id',$id)
        ->where('buyer_id',Auth::guard('web')->user()->id)->first();

        if(!is_null($order_details)){
            $order_includes = OrderInclude::where('order_id',$id)->get();
            $order_additionals = OrderAdditional::where('order_id',$id)->get();
            return view('frontend.user.buyer.order.order-details', compact('order_details','order_includes','order_additionals'));
        }
        abort(404);
    }

    public function orderCompleteRequestApprove($id=null)
    {
        Order::where('id',$id)->update(['order_complete_request'=>2,'status'=>2]);
        toastr_success(__('Order complete request successfully approved.'));
        return redirect()->back(); 
    }

    public function buyerProfile()
    {
        return view('frontend.user.buyer.profile.buyer-profile');
    }

    public function buyerProfileEdit(Request $request)
    {
        if ($request->isMethod('post')) {
            $user = Auth::guard('web')->user()->id;
            $request->validate([
                'name' => 'required|max:191',
                'email' => 'required|max:191|email|unique:users,email,'.$user,
                'phone' => 'required|max:191',
                'service_area' => 'required|max:191',
                'post_code' => 'required|max:191',
                'address' => 'required|max:191',
                'about' => 'required|max:5000',
            ]);
            $old_image = User::select('image')->where('id',Auth::guard('web')->user()->id)->first();
            User::where('id', Auth::guard('web')->user()->id)
                ->update([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'image' => $request->image ?? $old_image->image,
                    'profile_background' => $request->profile_background ?? $old_image->profile_background,
                    'service_city' => $request->service_city,
                    'service_area' => $request->service_area,
                    'country_id' => $request->country,
                    'post_code' => $request->post_code,
                    'address' => $request->address,
                    'about' => $request->about,
                ]);
            toastr_success(__('Profile Update Success---'));
            return redirect()->back();
        }
        
        $cities = ServiceCity::where('status',1)->get();
        $areas = ServiceArea::where('status',1)->get();
        $countries = Country::where('status',1)->get();
        return view('frontend.user.buyer.profile.buyer-profile-edit',compact('cities','areas','countries'));
    }

    public function buyerAccountSetting(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'current_password' => 'required|min:6',
                'new_password' => 'required|min:6',
                'confirm_password' => 'required|min:6',
            ]);

            $buyer = User::where('id', Auth::user()->id)->first();

            if (Hash::check($request->current_password, $buyer->password)) {
                if ($request->new_password == $request->confirm_password) {
                    User::where('id', $buyer->id)->update(['password' => Hash::make($request->new_password)]);
                    toastr_success(__('Password Update Success---'));
                    return redirect()->back();
                }
                toastr_error(__('Password and Confirm Password not match---'));
                return redirect()->back();
            }
            toastr_error(__('Current Password is Wrong---'));
            return redirect()->back();
        }
        return view('frontend.user.buyer.profile.buyer-account-settings');
    }

    public function buyerLogout()
    {
        Auth::logout();
        return redirect('/');
    }

    //support tickets
    public function allTickets()
    {
        $tickets = SupportTicket::where('buyer_id',Auth::guard('web')->user()->id)
            ->orderBy('id','desc')
            ->paginate(10);

        $orders = Order::where('buyer_id', Auth::guard('web')->user()->id)
            ->where('payment_status', '!=','')
            ->whereNotNull('buyer_id')
            ->latest()->get();

        return view('frontend.user.buyer.support-ticket.all-tickets', compact('tickets','orders'));
    }

    //add new ticket
    public function addNewTicket(Request $request, $id=null)
    {
        if($request->isMethod('post')){
            if($request->order_id){
                $seller_id = Order::select('seller_id')->where('id',$request->order_id)->first();
            }

            $this->validate($request,[
                'title' => 'required|string|max:191',
                'subject' => 'required|string|max:191',
                'priority' => 'required|string|max:191',
                'description' => 'required|string',
            ],[
                'title.required' => __('title required'),
                'subject.required' =>  __('subject required'),
                'priority.required' =>  __('priority required'),
                'description.required' => __('description required'),
            ]);

            SupportTicket::create([
                'title' => $request->title,
                'description' => $request->description,
                'subject' => $request->subject,
                'status' => 'open',
                'priority' => $request->priority,
                'buyer_id' => Auth::guard('web')->user()->id,
                'seller_id' => $seller_id->seller_id,
                'service_id' => $request->service_id,
                'order_id' => $request->order_id,
            ]);
            toastr_success(__('Ticket successfully created.'));
            $last_ticket_id = DB::getPdo()->lastInsertId();
            $last_ticket = SupportTicket::where('id',$last_ticket_id)->first();

            // send order ticket notification to seller
            $seller = User::where('id',$last_ticket->seller_id)->first();
            if($seller){
                $order_ticcket_message = __('You have a new order ticket');
                $seller ->notify(new TicketNotificationSeller($last_ticket_id , $seller_id, $last_ticket->seller_id,$order_ticcket_message ));
            }
            return redirect()->back();
        }

        $order = Order::select('id','service_id','seller_id')
            ->where('id',$id)
            ->where('buyer_id',Auth::guard('web')->user()->id)
            ->first();

        return view('frontend.user.buyer.support-ticket.add-new-ticket', compact('order'));
    }

    public function ticketDelete($id=null)
    {
        SupportTicket::find($id)->delete();
        toastr_error(__('Ticket Delete Success---'));
        return redirect()->back();
    }

    //view ticket
    public function view_ticket(Request $request,$id)
    {
        $ticket_details = SupportTicket::findOrFail($id);
        $all_messages = SupportTicketMessage::where(['support_ticket_id'=>$id])->get();
        $q = $request->q ?? '';
        foreach(Auth::guard('web')->user()->unreadNotifications as $notification){

            if($ticket_details->id == $notification->data['last_ticket_id']){
                $Notification = Auth::guard('web')->user()->Notifications->find($notification->id);
                if($Notification){
                    $Notification->markAsRead();
                }
                return view('frontend.user.buyer.support-ticket.view-ticket', compact('ticket_details','all_messages','q'));
            }
        }
        return view('frontend.user.buyer.support-ticket.view-ticket', compact('ticket_details','all_messages','q'));
    }

    //priority status 
    public function priorityChange(Request $request)
    {
        SupportTicket::where('id',$request->ticket_id)->update(['priority'=>$request->priority]);
        toastr_success(__('Priority Change Success---'));
        return redirect()->back();
    }

    //change status 
    public function statusChange($id=null)
    {
        $status = SupportTicket::find($id);
        if($status->status=='open'){
            $status = 'close';
        }else{
            $status = 'open';
        }
        SupportTicket::where('id',$id)->update(['status'=>$status]);
        toastr_success(__('Status Change Success---'));
        return redirect()->back();
    }

    //send message 
    public function support_ticket_message(Request $request)
    {
        $this->validate($request,[
            'ticket_id' => 'required',
            'user_type' => 'required|string|max:191',
            'message' => 'required',
            'send_notify_mail' => 'nullable|string',
            'file' => 'nullable|mimes:zip',
        ]);

        $ticket_info = SupportTicketMessage::create([
            'support_ticket_id' => $request->ticket_id,
            'type' => $request->user_type,
            'message' => $request->message,
            'notify' => $request->send_notify_mail ? 'on' : 'off',
        ]);

        if ($request->hasFile('file')){
            $uploaded_file = $request->file;
            $file_extension = $uploaded_file->extension();
            $file_name =  pathinfo($uploaded_file->getClientOriginalName(),PATHINFO_FILENAME).time().'.'.$file_extension;
            $uploaded_file->move('assets/uploads/ticket',$file_name);
            $ticket_info->attachment = $file_name;
            $ticket_info->save();
        }

        //send mail to user
        event(new SupportMessage($ticket_info));
        return redirect()->back()->with(FlashMsg::item_new(__('Message Send')));
    }


    //service review add
    public function serviceReviewfromDashboard(Request $request)
    {
        $request->validate([
            'rating' => 'required',
            'message' => 'required',
        ]);

        $review_count = Review::where('service_id',$request->service_id)
            ->where('buyer_id',Auth::guard('web')->user()->id)
            ->first();
        if(!$review_count){
            $review = Review::create([
                'service_id' => $request->service_id,
                'seller_id' => $request->seller_id,
                'buyer_id' => Auth::guard()->check() ? Auth::guard('web')->user()->id : NULL,
                'rating' => $request->rating,
                'name' => Auth::guard()->check() ? Auth::guard('web')->user()->name : NULL,
                'email' => Auth::guard()->check() ? Auth::guard('web')->user()->email : NULL,
                'message' => $request->message,
            ]);
            if($review){
                toastr_success(__('Review Added Success---'));
                return redirect()->back();
            }
        }
        toastr_error(__('You Can Not Send Review More Than One'));
        return redirect()->back();
    }
}
