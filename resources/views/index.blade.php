<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Product Inventory</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body>

<main class="container py-5">

    <h2 class="mb-4">Product Inventory</h2>
      {{-- Product submission form --}}
    <div class="card mb-5">
        <div class="card-header">
            Add New Product
        </div>

        <div class="card-body">

            <form id="productForm" action="{{ route('products.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="productName" class="form-label">
                        Product name
                    </label>

                    <input type="text"
                           name="name"
                           class="form-control"
                           id="productName"
                           value="{{ old('name') }}"
                           required>
                </div>

                <div class="mb-3">
                    <label for="quantity" class="form-label">
                        Quantity in stock
                    </label>

                    <input type="number"
                           name="quantity"
                           class="form-control"
                           id="quantity"
                           min="1"
                           value="{{ old('quantity') }}"
                           required>
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">
                        Price per item
                    </label>

                    <input type="number"
                           name="price"
                           class="form-control"
                           id="price"
                           step="0.01"
                           min="0"
                           value="{{ old('price') }}"
                           required>
                </div>

                <button type="submit" class="btn btn-primary">
                    Submit
                </button>

            </form>
            <div id="message" class="mt-3"></div>

        </div>
    </div>
    {{-- Products table --}}
    <h4 class="mb-3">Submitted Products</h4>

    <div class="table-responsive">

        <table class="table table-bordered table-striped align-middle">

            <thead class="table-dark">
                <tr>
                    <th>Product name</th>
                    <th>Quantity in stock</th>
                    <th>Price per item</th>
                    <th>Datetime submitted</th>
                    <th>Total value</th>
                    <th>Actions</th>
                </tr>
            </thead>

     <tbody id="productTable">

    @forelse($products as $product)

        <tr data-id="{{ $product['id'] }}">

            <td>{{ $product['name'] }}</td>

            <td>{{ $product['quantity'] }}</td>

            <td>{{ number_format($product['price'], 2) }}</td>

            <td data-created-at="{{ $product['created_at'] }}">
                {{ $product['created_at'] }}
            </td>

            <td>
                {{ number_format($product['quantity'] * $product['price'], 2) }}
            </td>

            <td>
                <button type="button"
                        class="btn btn-sm btn-outline-primary edit-btn">
                    Edit
                </button>
            </td>

        </tr>

    @empty

        <tr>
            <td colspan="6" class="text-center">
                No products submitted yet.
            </td>
        </tr>

    @endforelse

</tbody>


</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script>
    const productForm = document.getElementById('productForm');
    const productTable = document.getElementById('productTable');
    const message = document.getElementById('message');

    const updateUrl = "{{ route('products.update', ['id' => '__ID__']) }}";

    const csrfToken = document.querySelector('input[name="_token"]').value;


    // Display success/error messages
    function showMessage(text, type = 'success') {

        message.textContent = '';

        const alert = document.createElement('div');

        alert.className = `alert alert-${type}`;
        alert.textContent = text;

        message.appendChild(alert);

    }


    // Format date
    function formatDate(dateString) {

        return new Date(dateString.replace(' ', 'T')).toLocaleString();

    }


    // Calculate grand total
    function updateGrandTotal() {

        const rows = productTable.querySelectorAll('tr');

        let grandTotal = 0;

        rows.forEach(row => {

            if (row.cells.length < 6) return;

            const quantity = Number(row.cells[1].textContent);
            const price = Number(row.cells[2].textContent);

            grandTotal += quantity * price;

        });

        document.getElementById('grandTotal').textContent =
            grandTotal.toFixed(2);

    }


    // Render a product row
    function renderProductRow(row, product) {

        row.dataset.id = product.id;

        row.innerHTML = '';

        const values = [
            product.name,
            product.quantity,
            Number(product.price).toFixed(2),
            formatDate(product.created_at),
            (product.quantity * product.price).toFixed(2)
        ];

        values.forEach((value, index) => {

            const cell = document.createElement('td');

            cell.textContent = value;

            if (index === 3) {
                cell.dataset.createdAt = product.created_at;
            }

            row.appendChild(cell);

        });

        const actionCell = document.createElement('td');

        const editButton = document.createElement('button');

        editButton.type = 'button';
        editButton.className = 'btn btn-sm btn-outline-primary edit-btn';
        editButton.textContent = 'Edit';

        actionCell.appendChild(editButton);

        row.appendChild(actionCell);

    }

</script>
</body>
</html>

