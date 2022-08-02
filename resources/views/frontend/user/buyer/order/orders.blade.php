@extends('frontend.user.buyer.buyer-master')
@section('site-title')
    {{ __('Orders') }}
@endsection
@section('style')
    <style>
        .table-td-padding {
            border-collapse: separate;
            border-spacing: 10px 20px;
        }
    </style>
<link rel="stylesheet" href="{{asset('assets/common/css/themify-icons.css')}}">
<link rel="stylesheet" href="{{asset('assets/frontend/css/font-awesome.min.css')}}">
@endsection
@section('content')

    <x-frontend.seller-buyer-preloader/>

    <!-- Dashboard area Starts -->
    <div class="body-overlay"></div>
    <div class="dashboard-area dashboard-padding">
        <div class="container-fluid">
            <div class="dashboard-contents-wrapper">
                <div class="dashboard-icon">
                    <div class="sidebar-icon">
                        <i class="las la-bars"></i>
                    </div>
                </div>
                @include('frontend.user.buyer.partials.sidebar')
                @if($orders->count() >= 1)
                    <div class="dashboard-right">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="dashboard-settings margin-top-40">
                                    <h2 class="dashboards-title">{{ __('All Orders') }}</h2>
                                </div>
                                <x-msg.success/>
                                <x-msg.error/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 margin-top-40">
                                <div>
                                    <div class="table-responsive table-responsive--md">
                                        <table id="all_order_table" class="custom--table table-td-padding">
                                            <thead>
                                                <tr>
                                                    <th> {{ __('Order ID') }} </th>
                                                    <th> {{ __('Customer Name') }} </th>
                                                    <th> {{ __('Service Date') }} </th>
                                                    <th> {{ __('Service Time') }} </th>
                                                    <th> {{ __('Order Pricing') }} </th>
                                                    <th> {{ __('Amount Paid') }} </th>
                                                    <th> {{ __('Amount remaining') }} </th>
                                                    <th> {{ __('Order Status') }} </th>
                                                    <th> {{ __('Order Type') }} </th>
                                                    <th> {{ __('Order Complete Request') }} </th>
                                                    <th> {{ __('Action') }} </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($orders as $order)
                                                    <tr>
                                                        <td data-label="{{__('Order ID')}}"> {{ $order->id }} </td>
                                                        <td data-label="{{__('Customer Name')}}"> {{ $order->name }} </td>
                                                        <td data-label="{{__('Service Date')}}"> {{ Carbon\Carbon::parse($order->date)->format('d/m/y') }}</td>
                                                        <td data-label="{{__('Service Time')}}"> {{ $order->schedule }}</td>
                                                        <td data-label="{{__('Order Pricing')}}"> {{ float_amount_with_currency_symbol($order->total) }}</td>
                                                        <td data-label="{{__('Order Pricing')}}"> {{ float_amount_with_currency_symbol($order->pay_before) }}</td>
                                                        <td data-label="{{__('Order Pricing')}}"> {{ float_amount_with_currency_symbol($order->pay_after) }}</td>

                                                        @if ($order->status == 0) <td data-label="{{__('Order Status')}}" class="pending"><span>{{ __('Pending') }}</span></td>@endif
                                                        @if ($order->status == 1) <td data-label="{{__('Order Status')}}" class="order-active"><span>{{ __('Active') }}</span></td>@endif
                                                        @if ($order->status == 2) <td data-label="{{__('Order Status')}}" class="completed"><span>{{ __('Completed') }}</span></td>@endif
                                                        @if ($order->status == 3) <td data-label="{{__('Order Status')}}" class="order-deliver"><span>{{ __('Delivered') }}</span></td>@endif
                                                        @if ($order->status == 4) <td data-label="{{__('Order Status')}}" class="canceled"><span>{{ __('Cancelled') }}</span></td>@endif

                                                        <td data-label="Order Pricing">
                                                            @if($order->is_order_online==1)
                                                                <span class="btn btn-success">{{ __('Online') }}</span>
                                                            @else
                                                                <span class="btn btn-info">{{ __('OffLine') }}</span>
                                                            @endif
                                                        </td>

                                                        @if ($order->order_complete_request == 0) <td data-label="{{__('Order Status')}}" class="pending"><span>{{ __('No Request Create') }}</span></td>@endif
                                                        @if ($order->order_complete_request == 1) 
                                                        <td data-label="Order Status" class="pending">
                                                            <span>{{ __('Complete Request') }}</span>
                                                            <span><x-order-complete-request-approve :url="route('buyer.order.complete.request.approve',$order->id)"/></span>
                                                        </td>
                                                        @endif
                                                        @if ($order->order_complete_request == 2)
                                                            <td data-label="{{__('Order Status')}}" class="completed"><span>{{ __('Completed') }}</span></td>
                                                        @endif

                                                        <td data-label="Action">
                                                            @if ($order->status == 2)
                                                            <a href="#"
                                                               data-toggle="modal"
                                                               data-target="#reviewModal"
                                                                data-seller_id="{{ $order->seller_id }}"
                                                                data-service_id="{{ $order->service_id }}"
                                                                data-order_id="{{  $order->id }}"
                                                               class="review_add_modal"
                                                            >
                                                                <span class="icon eye-icon" data-toggle="tooltip" data-placement="top" title="{{ __('Review') }}">
                                                                    <i class="las la-star"></i>
                                                                </span>
                                                            </a>
                                                            @endif
                                                            <a href="#0" class="edit_status_modal" 
                                                            data-toggle="modal"
                                                            data-target="#editStatusModal" 
                                                            data-id="{{ $order->id }}"
                                                            data-status="{{ $order->status }}"
                                                            >
                                                            <span class="dash-icon color-1" data-toggle="tooltip" data-placement="top" title="{{ __('Change Status') }}"> 
                                                                <i class="las la-edit"></i>
                                                            </span>
                                                            </a>
                                                            <a href="{{ route('buyer.order.details', $order->id) }}">
                                                                <span class="icon eye-icon" data-toggle="tooltip" data-placement="top" title="{{ __('View Details') }}">
                                                                    <i class="las la-eye"></i>
                                                                </span>
                                                            </a>
                                                            @if($order->is_order_online != 1)
                                                                 @if($order->buyer_id != NULL)
                                                                    <a href="{{ route('buyer.support.ticket.new', $order->id) }}">
                                                                        <span class="icon eye-icon" data-toggle="tooltip" data-placement="top" title="{{ __('New Ticket') }}">
                                                                            <i class="las la-ticket-alt"></i>
                                                                        </span>
                                                                    </a>
                                                                @endif
                                                            @else 
                                                             <a href="{{ route('buyer.support.ticket.view',optional($order->online_order_ticket)->id) }}">
                                                                <span class="icon eye-icon" data-toggle="tooltip" data-placement="top" title="{{ __('View Ticket') }}">
                                                                    <i class="las la-eye-slash"></i>
                                                                </span>
                                                            </a>
                                                            @endif
                                                            <a href="{{ route('buyer.order.invoice.details',$order->id) }}">
                                                                <span class="icon print-icon" data-toggle="tooltip" data-placement="top" title="{{ __('Print Pdf') }}"> 
                                                                    <i class="las la-print"></i>
                                                                </span>
                                                            </a>
                                                        </td>    
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="blog-pagination margin-top-55">
                                        <div class="custom-pagination mt-4 mt-lg-5">
                                            {!! $orders->links() !!}
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                @else 
                <h2 class="no_data_found">{{ __('No Orders Found') }}</h2>
                @endif
            </div>
        </div>
    </div>

    <!--Status Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1" role="dialog" aria-labelledby="editModal"
         aria-hidden="true">
        <form action="{{ route('service.review.from.dashboard') }}" method="post">
            @csrf
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModal">{{ __('Review') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <div class="comments-flex-item">
                            <div class="single-commetns" style="font-size: 1em;">
                                <label class="comment-label"> {{ __('Ratings*') }} </label>
                                <div id="review"></div>
                            </div>
                            <input type="hidden" id="rating" name="rating" class="form-control form-control-sm">
                            <input type="hidden" id="seller_id" name="seller_id" class="form-control form-control-sm">
                            <input type="hidden" id="service_id" name="service_id" class="form-control form-control-sm">
                            <input type="hidden" id="order_id" name="order_id" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <label class="payout-request-note d-block pt-4" for="amount">{{ __('Comments') }}</label>
                            <textarea id="message" rows="5" name="message" class="form-control form--message" placeholder="{{ __('Post Comments') }}"></textarea>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Send Review') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

            <!--Status Modal -->
    <div class="modal fade" id="editStatusModal" tabindex="-1" role="dialog" aria-labelledby="editModal"
    aria-hidden="true">
        <form action="{{ route('buyer.change.order.status') }}" method="post">
            <input type="hidden" id="order_id_status" name="order_id">
            @csrf
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModal">{{ __('Change Status') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <ul class="mb-3 text-danger">
                            <li><strong>{{ __('Status Meaning:') }}</strong></li>
                            <li>{{ __('Pending: Did not start the job yet.') }}</li>
                            <li>{{ __('Active: Job already started.') }}</li>
                            <li>{{ __('Delivered: Order Deliverd For Checking.') }}</li>
                            <li>{{ __('Completed: Order is completed and closed.') }}</li>
                        </ul>

                        <div class="form-group">
                            <label for="up_day_id">{{ __('Select Status') }}</label>
                            <select name="status" id="status" class="form-control nice-select">
                                <option value="">{{ __('Select Status') }}</option>
                                {{-- <option value="0">{{ __('Pending') }}</option> --}}
                                <option value="1">{{ __('Active') }}</option>
                                {{-- <option value="2">{{ __('Completed') }}</option> --}}
                                {{-- <option value="3">{{ __('Delivered') }}</option> --}}
                                <option value="4">{{ __('Cancelled') }}</option>
                            </select>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

@endsection


@section('scripts')
    <script src="{{ asset('assets/backend/js/sweetalert2.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/rating.js') }}"></script>
    <script>
        (function($) {
            "use strict";

            $(document).ready(function() {
            //order complete status approve
            $(document).on('click','.swal_status_change',function(e){
                e.preventDefault();
                    Swal.fire({
                    title: '{{__("Are you sure to change status? Once you done you can not revert this !!")}}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: "{{__('Yes, change it!')}}"
                    }).then((result) => {
                    if (result.isConfirmed) {
                        $(this).next().find('.swal_form_submit_btn').trigger('click');
                    }
                    });
                });

                $(document).on('click', '.review_add_modal', function () {
                    let el = $(this);
                    let seller_id = el.data('seller_id');
                    let service_id = el.data('service_id');
                    let order_id = el.data('order_id');
                    let form = $('#reviewModal');
                    form.find('#seller_id').val(seller_id);
                    form.find('#service_id').val(service_id);
                    form.find('#order_id').val(order_id);
                });

                $("#review").rating({
                    "value": 5,
                    "click": function (e) {
                        $("#rating").val(e.stars);
                    }
                });

            });

            $(document).on('click', '.edit_status_modal', function(e) {
                console.log('orderId');
                e.preventDefault();
                let order_id = $(this).data('id');
                console.log('orderId', order_id);
                let status = $(this).data('status');

                $('#order_id_status').val(order_id);
                $('#status').val(status);
                $('.nice-select').niceSelect('update');
            });

        })(jQuery);
    </script>
@endsection
