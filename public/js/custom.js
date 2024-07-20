
$(document).ready(function () {
    $(".card-body").on('click', '.plus', function (e) {
        e.preventDefault();
        var card = $(this).closest(".card-body");
        var harga = card.find("#harga").val();
        var qty = card.find("#qty").val();
        var url = card.find("#harga").attr('data-url');
        var csrf = card.find("#harga").attr('data-csrf');
        var max = card.find("#qty").attr('max');

        if (parseInt(qty) + 1 > parseInt(max)) {
            alert('Jumlah barang tidak boleh melebihi stok.');
            card.find('#qty').val(max);
        } else {
            console.log('masuk sana');
            updateQuantityAjax( url, parseInt(qty) + 1, $(this).attr('data-csrf'));
            card.find("#qty").val(parseInt(qty) + 1);
        }

        var subtotal = parseInt(harga) * parseInt(card.find("#qty").val());
        card.find(".total").val(subtotal);

        if (parseInt(qty) >= 0) { // Pastikan qty tidak kurang dari 0
            card.find(".minus").prop("disabled", false);
        }
    });

    $(".card-body").on('click', '.minus', function (e) {
        e.preventDefault();
        var card = $(this).closest(".card-body");
        var harga = card.find("#harga").val();
        var qty = card.find("#qty").val();
        var url = card.find("#harga").attr('data-url');
        var csrf = card.find("#harga").attr('data-csrf');

        var tambah = parseInt(qty) - 1;
        card.find("#qty").val(Math.max(tambah, 1)); // Gunakan Math.max untuk memastikan tidak kurang dari 1

        var subtotal = parseInt(harga) * parseInt(card.find("#qty").val());
        card.find(".total").val(subtotal);

        updateQuantityAjax(url, Math.max(tambah, 1), csrf);

        if (parseInt(card.find("#qty").val()) <= 1) {
            card.find(".minus").prop("disabled", true);
        }
    });

    $(".card-body").on('input', '#qty', function (e) {
        var card = $(this).closest(".card-body");
        var url = card.find("#harga").attr('data-url');
        var qty = card.find("#qty").val();
        var max = card.find("#qty").attr('max');
        
        // Memastikan qty tidak kurang dari 1
        qty = Math.max(parseInt(qty), 1);
        
        // Memastikan qty tidak dapat diisi dengan nilai 0
        if (!qty) {
            alert('Pembelian barang minimal 1.');
            card.find('#qty').val(1);
        } else if (parseInt(qty) > parseInt(max)) {
            alert('Jumlah barang tidak boleh melebihi stok.');
            card.find('#qty').val(max);
        } else {
            console.log('masuk sana');
        
            // Mengambil kembali nilai harga
            var harga = card.find("#harga").val();
    
            // Pemeriksaan apakah harga dan qty adalah angka yang valid
            if (!isNaN(harga) && !isNaN(qty)) {
                var subtotal = parseInt(harga) * qty;
                card.find(".total").val(subtotal);
                updateQuantityAjax(url, qty, $(this).attr('data-csrf'));
            } else {
                console.error('Harga atau qty bukan angka yang valid.');
            }
        }
    });
    
    
    
    

    $(".card-body").each(function () {
        var card = $(this);
        var harga = card.find("#harga").val();
        var qty = card.find("#qty").val();
        var total = parseInt(harga) * parseInt(qty);
        card.find("#total").val(total);
    });

    function updateQuantityAjax(url, quantity, csrf) {
        $.ajax({
            url: url,
            data: {
                qty: quantity,
                _token: csrf
            },
            success: function (res) {
                console.log('berhasil');
            },
            error: function (err) {
                console.log('gagal');
            }
        });
    }
});
