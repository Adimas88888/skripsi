@extends('pelanggan.layout.index')

@section('conten')
    <style>
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>
    <h3 class="mt-5 mb-5">Keranjang Belanja</h3>
    <a href="{{ route('checkout.product') }}"
        class="btn btn-success text-center d-flex flex-column align-items-center flex-sm-fill mt-5px mb-5">
        <i class="fas fa-shopping-cart"></i>
        Checkout All
    </a>
    @if (!$data)
    @else
        @foreach ($data as $item)
            <div class="card mb-3">
                <div class="card-body d-flex flex-column flex-md-row align-items-center gap-4">
                    <img src="{{ asset('storage/product/' . $item->product->foto) }}" alt="{{ $item->product->nama_product }}"
                        style="width: 200px; height: 200px;">
                    <form action="{{ route('checkout.product', ['id' => $item->id]) }}" method="POST" class="w-100">
                        @csrf
                        <div class="desc">
                            <p class="fs-4 fw-bold">{{ $item->product->nama_product }}</p>
                            <p class="fs-4 with-margin">Stok Barang {{ $item->product->quantity }}</p>

                            <input type="hidden" name="idBarang" value="{{ $item->product->id }}">
                            <input type="number" class="form-control border-0 fs-2" name="harga" id="harga"
                                data-url="{{ route('checkout.update-quantity', $item->id) }}" readonly
                                data-csrf="{{ csrf_token() }}" value="{{ $item->product->harga }}">
                            <div class="row mb-3">
                                <label for="qty" class="col-sm-3 col-form-label fs-5">Quantity</label>
                                <div class="col-sm-5 d-flex">
                                    <button class="rounded-start bg-secondary p-2 border border-0 plus"
                                        id="plus">+</button>
                                    <input type="number" name="qty" class="form-control w-50 text-center"
                                        id="qty" value="{{ $item->qty }}" max="{{ $item->product->quantity }}" min="1" >

                                    {{-- <input type="number" name="qty" class="form-control w-50 text-center"
                                        id="qty" name="qty" value="{{ $item->qty }}" disabled max="{{ $item->product->quantity }}"> --}}
                                    <button class="rounded-end bg-secondary p-2 border border-0 minus" id="minus"
                                        disabled>-</button>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="price" class="col-sm-3 col-form-label fs-5">Total</label>
                                <input type="text" class="col-sm-5 form-control w-50 border-0 fs-4 total" name="total"
                                    readonly id="total">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between w-100 gap-5">
                            <button type="button"
                                class="btn btn-danger delete-btn text-center d-flex flex-column align-items-center flex-sm-fill"
                                data-item-id="{{ $item->id }}">
                                <i class="fas fa-trash-alt"></i>
                                Delete
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        @endforeach
        <script>
            // Menambahkan event listener untuk tombol "Delete"
            let tombolDelete = document.querySelectorAll('.delete-btn');
            tombolDelete.forEach(button => {
                console.log('sini');
                button.addEventListener('click', function() {
                    const itemId = this.getAttribute('data-item-id');

                    // Tampilkan pesan swal untuk konfirmasi penghapusan
                    Swal.fire({
                        title: 'Anda yakin ingin menghapus item ini?',
                        text: 'Item yang dihapus tidak dapat dikembalikan.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Kirim permintaan DELETE ke server
                            fetch(`/delete/detailtransaksi/${itemId}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                })
                                .then(response => {
                                    console.log(response);
                                    if (response.status === 200) {
                                        // Hapus elemen dari tampilan setelah berhasil dihapus
                                        const itemCard = this.closest('.card');
                                        itemCard.remove();

                                        // Tampilkan toast success
                                        toastr.success('Item berhasil dihapus', 'Sukses');
                                    } else {
                                        // Tampilkan pesan swal untuk kegagalan
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Gagal menghapus item',
                                            text: 'Terjadi kesalahan saat menghapus item.',
                                        });
                                    }
                                });
                        }
                    });
                });
            });
        </script>
    @endif
@endsection
