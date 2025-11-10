@extends('website.app')

@section('contents')
    <section class="product-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="product-category-title">
                        <h2>{{ $category }}</h2>
                    </div>
                </div>
            </div>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-2 g-4">


                @php
                    $total_items = $product_models->pluck('items')->flatten()->count();
                @endphp

                @if ($total_items == 0)
                    <div class="col-12 text-center py-5">
                        <h4 style="color:#999;">No products found.</h4>
                    </div>
                @endif



                @foreach ($product_models as $model)
                    <div class="col">
                        <div class="product-item">
                            <div class="product-title">
                                <h3>{{ $model->name }}</h3>
                                <span class="product-size">{{ $model->size }}</span>
                            </div>

                            <div class="varieant-wrap">
                                @foreach ($model->items as $variant)
                                    <div class="varient-box">
                                        <a href="{{ asset($variant->image_url) }}" data-featherlight="image">
                                            <img src="{{ asset($variant->image_url) }}" alt="">
                                        </a>
                                        <div class="varient-info">
                                            <h4>{{ $variant->code }}. {{ $variant->name }}</h4>
                                            <div class="choice-item">
                                                <span class="decrease-v"><img src="{{ asset('website/img/minus.svg') }}"
                                                        alt=""></span>
                                                <input type="text" value="0" class="quantity-input"
                                                    data-variant-id="{{ $variant->id }}">
                                                <span class="increase-v"><img src="{{ asset('website/img/plus.svg') }}"
                                                        alt=""></span>
                                            </div>

                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="product-action">
                                <button class="action-btn size-info-btn" data-bs-toggle="modal"
                                    data-bs-target="#sizeInfoModal" data-size-img="{{ asset($model->image_url) }}">
                                    Size Info
                                </button>
                                <button class="action-btn add-cart-btn" type="button">
                                    Add to Cart
                                </button>
                            </div>
                        </div>

                    </div>
                @endforeach

            </div>
        </div>
    </section>

    <!-- Size Info Modal -->
    <div class="modal modal__md fade" id="sizeInfoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <button class="modal_x" data-bs-dismiss="modal"><img src="assets/img/times.svg" alt=""></button>
                <div class="modal-body">
                    <div class="size-info-box">
                        <h4>Size Info</h4>
                        <img id="sizeInfoImage" src="" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add to Cart Modal -->
    <div class="modal modal__md fade" id="CartInfoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <button class="modal_x" data-bs-dismiss="modal"><img src="assets/img/times.svg" alt=""></button>
                <div class="modal-body">
                    <div class="cart-info-box">
                        <img src="assets/img/cart-success.svg" alt="">
                        <h2>Added to Cart!</h2>
                        <p>Item has been successfully added to your cart</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
@endpush
