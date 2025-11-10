@extends('website.app')

@section('contents')
    <section class="login-screen-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="shipping-wrapper">
                        <div class="shipping-title mb-0">
                            <h2>Shopping Cart</h2>
                            <p>Cart items: {{ count($carts) }}</p>
                        </div>
                        <div class="cart-list-table table-responsive" id="style-3">

                            <table class="table table-striped">
                                @if (count($carts) > 0)
                                    <thead>
                                        <tr>
                                            <th scope="col">
                                                <div class="all-select">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value=""
                                                            id="flexCheckDefault">
                                                        <label class="form-check-label" for="flexCheckDefault">Select
                                                            all</label>
                                                    </div>
                                                </div>
                                            </th>
                                            <th scope="col">Category</th>
                                            <th scope="col">Item</th>
                                            <th scope="col">Color</th>
                                            <th scope="col">Quantity</th>
                                            <th scope="col">Price</th>
                                            <th scope="col">Discount</th>
                                            <th scope="col">Total</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        @foreach ($carts as $cart)
                                            <tr data-id="{{ $cart->id }}">
                                                <td>
                                                    <div class="select-item">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" value=""
                                                                id="check{{ $cart->id }}">
                                                            <label class="form-check-label"
                                                                for="check{{ $cart->id }}"></label>
                                                        </div>
                                                        <div class="item-thumb">
                                                            <a href="{{ asset($cart->productItem->image_url) }}"
                                                                data-featherlight="image">
                                                                <img src="{{ asset($cart->productItem->image_url) }}"
                                                                    alt="">
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><span
                                                        class="category-btn">{{ $cart->productItem->productModel->name }}</span>
                                                </td>
                                                <td>
                                                    <h4>{{ $cart->productItem->code }}</h4>
                                                </td>
                                                <td>{{ $cart->productItem->name }}</td>
                                                <td>
                                                    <div class="quantity-wrap">
                                                        <div class="quantity-box">
                                                            <div class="choice-item">
                                                                <span class="decrease-v"><img
                                                                        src="{{ asset('website/img/minus.svg') }}"
                                                                        alt=""></span>
                                                                <input type="text" value="{{ $cart->quantity }}"
                                                                    data-cart-id="{{ $cart->id }}">
                                                                <span class="increase-v"><img
                                                                        src="{{ asset('website/img/plus.svg') }}"
                                                                        alt=""></span>
                                                            </div>
                                                        </div>
                                                        <button class="item__remove">Remove</button>
                                                    </div>
                                                </td>
                                                <td class="item-price">{{ number_format($cart->price, 2) }}</td>
                                                <td class="item-discount">
                                                    {{ $cart->productItem->discount_percentage ?? 0 }} %</td>
                                                <td class="item-total">0.00</td>
                                            </tr>
                                        @endforeach

                                    </tbody>
                                @else
                                    <tr class="mt-3">
                                        <td colspan="8" class="text-center py-5 mt-3">
                                            <img src="{{ asset('website/img/empty-cart.svg') }}" alt="Empty Cart"
                                                style="max-width:150px; margin-bottom:15px;">
                                            <h4>Your cart is empty</h4>
                                            <p>Browse our products and add something to your cart!</p>
                                            <a href="{{ url('/') }}" class="common-btn btn-black mt-3">Continue
                                                Shopping</a>
                                        </td>
                                    </tr>
                                @endif


                            </table>

                        </div>
                        <div class="purches-info">
                            <ul>
                                <li>Subtotal Quantity: <span><b class="subtotal-quantity">0</b></span></li>
                                <li>Subtotal: <span><b class="subtotal-amount">0.00</b></span></li>
                                <li>Shipping: <span>Calculated based on location & quantity</span></li>
                            </ul>
                        </div>
                        <div class="total-ammount-info">
                            <div class="total-ammount-text">
                                <h2>Total Amount:</h2>
                                <span>(Pricing is subject to the purchase quantity)</span>
                            </div>
                            <div class="ammount-text">
                                <h2 class="total-amount">0.00 + Shipping</h2>
                            </div>
                        </div>
                    </div>
                    <div class="whatsapp-contact">
                        <a href="#" class="common-btn btn-black whatsapp-btn"><img
                                src="{{ asset('website/img/whatsapp.svg') }}" alt="">Continue on WhatsApp</a>
                    </div>
                </div>
            </div>
        </div>
    </section>


@endsection

<!-- Toast Container -->
    <div style="margin-top:  75px !important" class="position-fixed  end-0 p-3" style="z-index: 1100;">
        <div id="cartToast" class="toast align-items-center text-bg-primary border-0" role="alert" aria-live="assertive"
            aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="cartToastBody">Toast message</div>
                <button type="button" class="btn-close btn-close-black me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    </div>

{{-- js --}}

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const cartToastEl = document.getElementById('cartToast');
            const cartToast = new bootstrap.Toast(cartToastEl);

            function showToast(message, type = 'danger') {
                cartToastEl.className = `toast align-items-center text-bg-${type} border-0`;
                document.getElementById('cartToastBody').innerHTML = message;
                cartToast.show();
            }

            const selectAllCheckbox = document.getElementById('flexCheckDefault');
            let rowToRemove = null;

            // Select All
            selectAllCheckbox.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('tbody .form-check-input');
                checkboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
                updateTotals();
            });

            // Individual checkbox
            document.querySelectorAll('tbody .form-check-input').forEach(cb => {
                cb.addEventListener('change', function() {
                    const allChecked = Array.from(document.querySelectorAll(
                        'tbody .form-check-input')).every(i => i.checked);
                    selectAllCheckbox.checked = allChecked;
                    updateTotals();
                });
            });

            // Event delegation
            document.addEventListener('click', function(event) {

                // Increase
                if (event.target.closest('.increase-v')) {
                    const btn = event.target.closest('.increase-v');
                    const input = btn.previousElementSibling;
                    const row = btn.closest('tr');
                    input.value = parseInt(input.value || 0) + 1;
                    updateCart(row, parseInt(input.value));
                }

                // Decrease
                if (event.target.closest('.decrease-v')) {
                    const btn = event.target.closest('.decrease-v');
                    const input = btn.nextElementSibling;
                    const row = btn.closest('tr');
                    input.value = Math.max(1, parseInt(input.value || 0) - 1);
                    updateCart(row, parseInt(input.value));
                }

                // Remove item
                if (event.target.closest('.item__remove')) {
                    rowToRemove = event.target.closest('tr');
                    const itemName = rowToRemove.querySelector('h4').textContent;
                    showToast(
                        `Delete <b>${itemName}</b>? <button id="toastRemoveBtn" class="btn btn-sm btn-danger ms-2">Remove</button>`,
                        'warning');
                }

                // Confirm remove inside toast
                if (event.target.id === 'toastRemoveBtn' && rowToRemove) {
                    const itemId = rowToRemove.dataset.id;
                    fetch("{{ route('website.cart.remove') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                id: itemId
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                rowToRemove.remove();
                                updateTotals();
                                showToast('Item removed from cart', 'success');
                            } else {
                                showToast(data.message || 'Failed to remove item', 'danger');
                            }
                            rowToRemove = null;
                            cartToast.hide();
                        });
                }



                // WhatsApp
                // if (event.target.closest('.whatsapp-btn')) {
                //     event.preventDefault();
                //     let message = 'My Cart:\n';
                //     let anySelected = false;
                //     let subtotalBeforeDiscount = 0;
                //     let totalDiscount = 0;
                //     let subtotalAfterDiscount = 0;

                //     document.querySelectorAll('tbody tr').forEach(row => {
                //         const checkbox = row.querySelector('.form-check-input');
                //         if (!checkbox.checked) return;

                //         anySelected = true;

                //         const itemName = row.querySelector('.category-btn').textContent;
                //         const code = row.querySelector('h4').textContent;
                //         const qty = parseInt(row.querySelector('input[type="text"]').value) || 0;
                //         const price = parseFloat(row.querySelector('.item-price').textContent) || 0;
                //         const discount = parseFloat(row.querySelector('.item-discount')
                //             ?.textContent || 0);

                //         const totalBeforeDiscount = price * qty;
                //         const discountAmount = totalBeforeDiscount * (discount / 100);
                //         const totalAfterDiscount = totalBeforeDiscount - discountAmount;

                //         subtotalBeforeDiscount += totalBeforeDiscount;
                //         totalDiscount += discountAmount;
                //         subtotalAfterDiscount += totalAfterDiscount;

                //         message += `${itemName} (${code}): ${qty} x ${price.toFixed(2)}`;
                //         if (discount > 0) message += ` (-${discountAmount.toFixed(2)} discount)`;
                //         message += ` = ${totalAfterDiscount.toFixed(2)}\n`;
                //     });

                //     if (!anySelected) {
                //         showToast('Please select at least one item', 'danger');
                //         return;
                //     }

                //     message += `\nSubtotal (Before Discount): ${subtotalBeforeDiscount.toFixed(2)}`;
                //     message += `\nTotal Discount: ${totalDiscount.toFixed(2)}`;
                //     message += `\nTotal Amount: ${subtotalAfterDiscount.toFixed(2)} + Shipping`;

                //     const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
                //     const phoneNumber = "8801330477445";
                //     const text = encodeURIComponent(message);
                //     const whatsappUrl = isMobile ?
                //         `https://wa.me/${phoneNumber}?text=${text}` :
                //         `https://web.whatsapp.com/send?phone=${phoneNumber}&text=${text}`;
                //     window.open(whatsappUrl, '_blank');
                // }

                // if (event.target.closest('.whatsapp-btn')) {
                //     event.preventDefault();

                //     let cartData = [];
                //     let subtotalAfterDiscount = 0;

                //     document.querySelectorAll('tbody tr').forEach(row => {
                //         const checkbox = row.querySelector('.form-check-input');
                //         if (!checkbox.checked) return;

                //         const productId = row.dataset.id;
                //         const qty = parseInt(row.querySelector('input[type="text"]').value) || 0;
                //         const price = parseFloat(row.querySelector('.item-price').textContent) || 0;
                //         const discountPercentage = parseFloat(row.querySelector('.item-discount')
                //             .textContent) || 0;

                //         const totalBeforeDiscount = price * qty;
                //         const discountAmount = totalBeforeDiscount * (discountPercentage / 100);
                //         const totalAfterDiscount = totalBeforeDiscount - discountAmount;

                //         subtotalAfterDiscount += totalAfterDiscount;

                //         cartData.push({
                //             product_id: productId,
                //             quantity: qty,
                //             price: price,
                //             discount: discountAmount,
                //             total: totalAfterDiscount
                //         });
                //     });

                //     if (cartData.length === 0) {
                //         showToast('Please select at least one item', 'danger');
                //         return;
                //     }


                //     fetch("{{ route('website.order.store') }}", {
                //             method: 'POST',
                //             headers: {
                //                 'Content-Type': 'application/json',
                //                 'X-CSRF-TOKEN': "{{ csrf_token() }}"
                //             },
                //             body: JSON.stringify({
                //                 cart: cartData,
                //                 total_amount: subtotalAfterDiscount
                //             })
                //         })
                //         .then(res => res.json())
                //         .then(data => {
                //             if (data.success) {
                //                 showToast('Order saved successfully!', 'success');

                //                 // 2️⃣ Prepare WhatsApp message
                //                 let message = 'My Cart:\n';

                //                 let subtotalBeforeDiscount = 0;
                //                 let totalDiscount = 0;

                //                 cartData.forEach(item => {
                //                     const totalBeforeDiscount = item.price * item.quantity;
                //                     subtotalBeforeDiscount += totalBeforeDiscount;
                //                     totalDiscount += item.discount;

                //                     message +=
                //                         `${item.product_id}: ${item.quantity} x ${item.price.toFixed(2)}`;

                //                     if (item.discount > 0) {
                //                         message += ` (-${item.discount.toFixed(2)} discount)`;
                //                     }

                //                     message += ` = ${item.total.toFixed(2)}\n`;
                //                 });

                //                 message +=
                //                     `\nSubtotal (Before Discount): ${subtotalBeforeDiscount.toFixed(2)}\n`;
                //                 message += `Total Discount: ${totalDiscount.toFixed(2)}\n`;
                //                 message +=
                //                     `Total Amount: ${subtotalAfterDiscount.toFixed(2)} + Shipping`;

                //                 // 3️⃣ Open WhatsApp after order saved
                //                 const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
                //                 const phoneNumber = "8801330477445";
                //                 const text = encodeURIComponent(message);
                //                 const whatsappUrl = isMobile ?
                //                     `https://wa.me/${phoneNumber}?text=${text}` :
                //                     `https://web.whatsapp.com/send?phone=${phoneNumber}&text=${text}`;
                //                 window.open(whatsappUrl, '_blank');

                //             } else {
                //                 showToast(data.message || 'Failed to save order', 'danger');
                //             }
                //         });

                // }

                if (event.target.closest('.whatsapp-btn')) {
                    event.preventDefault();

                    let cartData = [];
                    let subtotalAfterDiscount = 0;

                    document.querySelectorAll('tbody tr').forEach(row => {
                        const checkbox = row.querySelector('.form-check-input');
                        if (!checkbox.checked) return;

                        const productName = row.querySelector('.category-btn')
                            .textContent; // Product name
                        const code = row.querySelector('h4').textContent;
                        const qty = parseInt(row.querySelector('input[type="text"]').value) || 0;
                        const price = parseFloat(row.querySelector('.item-price').textContent) || 0;
                        const discountPercentage = parseFloat(row.querySelector('.item-discount')
                            .textContent) || 0;

                        const totalBeforeDiscount = price * qty;
                        const discountAmount = totalBeforeDiscount * (discountPercentage / 100);
                        const totalAfterDiscount = totalBeforeDiscount - discountAmount;

                        subtotalAfterDiscount += totalAfterDiscount;

                        cartData.push({
                            product_id: row.dataset.id,
                            product_name: productName,
                            code: code,
                            quantity: qty,
                            price: price,
                            discount: discountAmount,
                            total: totalAfterDiscount
                        });
                    });

                    if (cartData.length === 0) {
                        showToast('Please select at least one item', 'danger');
                        return;
                    }

                    // Save order
                    fetch("{{ route('website.order.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                cart: cartData,
                                total_amount: subtotalAfterDiscount
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                showToast('Order saved successfully!', 'success');

                                // Prepare WhatsApp message
                                let message = 'My Cart:\n';
                                let subtotalBeforeDiscount = 0;
                                let totalDiscount = 0;

                                cartData.forEach(item => {
                                    const totalBeforeDiscount = item.price * item.quantity;
                                    subtotalBeforeDiscount += totalBeforeDiscount;
                                    totalDiscount += item.discount;

                                    message +=
                                        `${item.product_name} (${item.code}): ${item.quantity} x ${item.price.toFixed(2)}`;
                                    if (item.discount > 0) {
                                        message += ` (-${item.discount.toFixed(2)} discount)`;
                                    }
                                    message += ` = ${item.total.toFixed(2)}\n`;
                                });

                                message +=
                                    `\nSubtotal (Before Discount): ${subtotalBeforeDiscount.toFixed(2)}\n`;
                                message += `Total Discount: ${totalDiscount.toFixed(2)}\n`;
                                message +=
                                    `Total Amount: ${subtotalAfterDiscount.toFixed(2)} + Shipping\n\n`;


                                // Open WhatsApp
                                const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
                                const phoneNumber = "8801330477445";
                                const text = encodeURIComponent(message);
                                const whatsappUrl = isMobile ?
                                    `https://wa.me/${phoneNumber}?text=${text}` :
                                    `https://web.whatsapp.com/send?phone=${phoneNumber}&text=${text}`;
                                window.open(whatsappUrl, '_blank');

                                // Optional: Auto redirect to thank-you page after 3 sec
                                setTimeout(() => {
                                    window.location.href =
                                        "{{ route('website.order.thankyou') }}";
                                }, 3000);

                            } else {
                                showToast(data.message || 'Failed to save order', 'danger');
                            }
                        });
                }




            });

            // Update cart quantity
            function updateCart(row, quantity) {
                const itemId = row.dataset.id;
                fetch("{{ route('website.cart.update') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            id: itemId,
                            quantity
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            row.querySelector('.item-total').textContent = data.item_total;
                            updateTotals();
                            showToast('Cart updated successfully ! ', 'info');
                        } else {
                            showToast(data.message || 'Failed to update cart', 'danger');
                        }
                    });
            }

            // Update totals
            // function updateTotals() {
            //     let subtotalQty = 0;
            //     let subtotal = 0;
            //     document.querySelectorAll('tbody tr').forEach(row => {
            //         const checkbox = row.querySelector('.form-check-input');
            //         if (!checkbox.checked) return;
            //         const qty = parseInt(row.querySelector('input[type="text"]').value) || 0;
            //         const price = parseFloat(row.querySelector('.item-price').textContent) || 0;
            //         const total = price * qty;
            //         row.querySelector('.item-total').textContent = total.toFixed(2);
            //         subtotalQty += qty;
            //         subtotal += total;
            //     });
            //     document.querySelector('.subtotal-quantity').textContent = subtotalQty;
            //     document.querySelector('.subtotal-amount').textContent = subtotal.toFixed(2);
            //     document.querySelector('.total-amount').textContent = subtotal.toFixed(2) + ' + Shipping';
            // }


            function updateTotals() {
                let subtotalQty = 0;
                let subtotal = 0;
                document.querySelectorAll('tbody tr').forEach(row => {
                    const checkbox = row.querySelector('.form-check-input');
                    if (!checkbox.checked) return;

                    const qty = parseInt(row.querySelector('input[type="text"]').value) || 0;
                    const price = parseFloat(row.querySelector('.item-price').textContent) || 0;
                    const discountPercentage = parseFloat(row.querySelector('.item-discount')
                        .textContent) || 0;

                    const totalBeforeDiscount = price * qty;
                    const discountAmount = totalBeforeDiscount * (discountPercentage / 100);
                    const totalAfterDiscount = totalBeforeDiscount - discountAmount;

                    row.querySelector('.item-total').textContent = totalAfterDiscount.toFixed(2);

                    subtotalQty += qty;
                    subtotal += totalAfterDiscount;
                });

                document.querySelector('.subtotal-quantity').textContent = subtotalQty;
                document.querySelector('.subtotal-amount').textContent = subtotal.toFixed(2);
                document.querySelector('.total-amount').textContent = subtotal.toFixed(2) + ' + Shipping';
            }


            // Init totals
            updateTotals();

        });
    </script>
@endpush
