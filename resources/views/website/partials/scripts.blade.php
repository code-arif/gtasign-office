<script src="{{ asset('website/js/jquery.min.js') }} "></script>
<script src="{{ asset('website/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('website/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('website/js/main.js') }}"></script>

<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    (function($) {
        "use strict";

        jQuery(document).ready(function($) {

            function initSetupSlider() {
                if ($(window).width() < 768) { // only mobile
                    if (!$('.setup-box-wrap').hasClass('owl-loaded')) {
                        $('.setup-box-wrap').owlCarousel({
                            items: 1,
                            margin: 15,
                            loop: false,
                            nav: true,
                            dots: true
                        });
                    }
                } else {
                    // destroy owl on larger screens
                    if ($('.setup-box-wrap').hasClass('owl-loaded')) {
                        $('.setup-box-wrap').trigger('destroy.owl.carousel').removeClass(
                            'owl-loaded owl-hidden');
                        $('.setup-box-wrap').find('.owl-stage-outer').children().unwrap();
                    }
                }
            }

            initSetupSlider();
            $(window).on('resize', initSetupSlider);

        });
    })(jQuery);
</script>

<script>
    $('#sizeInfoModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget) // Button that triggered the modal
        var sizeImg = button.data('size-img') // Extract info from data-* attributes
        var modal = $(this)
        modal.find('.modal-body img').attr('src', sizeImg);
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function() {

        // whether the user is authenticated (rendered by Blade)
        const isLoggedIn = @json(Auth::check());

        // Increase / Decrease quantity
        document.querySelectorAll('.increase-v').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = btn.previousElementSibling;
                input.value = parseInt(input.value || 0);
            });
        });

        document.querySelectorAll('.decrease-v').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = btn.nextElementSibling;
                if (parseInt(input.value) > 0) input.value = parseInt(input.value);
            });
        });

        // Size Info Modal
        const sizeModal = document.getElementById('sizeInfoModal');
        const sizeImage = document.getElementById('sizeInfoImage');
        sizeModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const imgSrc = button.getAttribute('data-size-img');
            sizeImage.src = imgSrc;
        });

        document.querySelectorAll('.add-cart-btn').forEach(btn => {
            btn.addEventListener('click', function() {

                // If not logged in, redirect to login page
                if (!isLoggedIn) {
                    window.location.href = "{{ route('login') }}";
                    return;
                }

                let items = [];
                document.querySelectorAll('.quantity-input').forEach(input => {
                    const qty = parseInt(input.value);
                    if (qty > 0) { // Only include items with quantity > 0
                        items.push({
                            id: input.dataset.variantId,
                            quantity: qty
                        });
                    }
                });

                if (items.length === 0) {
                    toastr.warning(
                        'Please select at least one item with a quantity greater than 0');
                    return;
                }

                fetch("{{ route('website.cart.store') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            items
                        })
                    })
                    .then(async res => {
                        const data = await res.json();

                        if (res.ok && data.success) {
                            // Success: show modal and toastr
                            var cartModal = new bootstrap.Modal(document.getElementById(
                                'CartInfoModal'));
                            cartModal.show();
                            document.querySelectorAll('.quantity-input').forEach(
                                input => input.value = 0);

                            toastr.success('Items added to cart successfully');
                        } else {
                            // Show error message from backend
                            toastr.error(data.message || 'Failed to add items to cart');
                        }
                    })
                    .catch(err => {
                        toastr.error('Error adding to cart');
                        console.error(err);
                    });
            });
        });



    });
</script>




@stack('scripts')
