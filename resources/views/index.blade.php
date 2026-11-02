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
    {{-- Success message --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Validation errors --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

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


          <tfoot>
        <tr>
            <td colspan="4" class="text-end">
                <strong>Grand Total:</strong>
            </td>
            <td colspan="2">
                <strong id="grandTotal">
                    {{ number_format($grandTotal, 2) }}
                </strong>
            </td>
        </tr>
    </tfoot>
</table>
</div>

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

    // Add new product to the table
    function addProductToTable(product) {

        const emptyRow = productTable.querySelector('tr');

        if (emptyRow && emptyRow.cells.length === 1) {
            emptyRow.remove();
        }

        const row = document.createElement('tr');

        renderProductRow(row, product);

        // Add newest product at the top
        productTable.prepend(row);

        updateGrandTotal();

    }

    // AJAX form submission
    productForm.addEventListener('submit', async function(e) {

        e.preventDefault();

        const formData = new FormData(productForm);

        try {

            const response = await fetch("{{ route('products.store') }}", {

                method: 'POST',

                headers: {
                    'Accept': 'application/json'
                },

                body: formData

            });

            const data = await response.json();

            if (!response.ok) {
                throw data;
            }

            showMessage(data.message);

            addProductToTable(data.product);

            productForm.reset();

        } catch(error) {

            showMessage(
                error.errors
                    ? Object.values(error.errors).flat().join(' ')
                    : error.message || 'Something went wrong.',
                'danger'
            );

        }

    });


    // Handle Edit button clicks
    productTable.addEventListener('click', function(e) {

        if (!e.target.classList.contains('edit-btn')) {
            return;
        }

        const row = e.target.closest('tr');

        // Prevent opening multiple edit forms
        if (row.querySelector('.save-btn')) {
            return;
        }

        // Get existing values
        const product = {
            id: row.dataset.id,
            name: row.cells[0].textContent,
            quantity: row.cells[1].textContent,
            price: row.cells[2].textContent,
            created_at: row.cells[3].dataset.createdAt
        };


        // Replace product name with input
        const nameInput = document.createElement('input');

        nameInput.type = 'text';
        nameInput.className = 'form-control';
        nameInput.value = product.name;
        nameInput.required = true;

        row.cells[0].replaceChildren(nameInput);


        // Replace quantity with input
        const quantityInput = document.createElement('input');

        quantityInput.type = 'number';
        quantityInput.className = 'form-control';
        quantityInput.min = '1';
        quantityInput.value = product.quantity;
        quantityInput.required = true;

        row.cells[1].replaceChildren(quantityInput);


        // Replace price with input
        const priceInput = document.createElement('input');

        priceInput.type = 'number';
        priceInput.className = 'form-control';
        priceInput.min = '0';
        priceInput.step = '0.01';
        priceInput.value = product.price;
        priceInput.required = true;

        row.cells[2].replaceChildren(priceInput);


        // Replace Edit button with Save and Cancel
        const actionCell = row.cells[5];

        actionCell.replaceChildren();

        const saveButton = document.createElement('button');

        saveButton.type = 'button';
        saveButton.className = 'btn btn-sm btn-success save-btn me-1';
        saveButton.textContent = 'Save';

        const cancelButton = document.createElement('button');

        cancelButton.type = 'button';
        cancelButton.className = 'btn btn-sm btn-secondary cancel-btn';
        cancelButton.textContent = 'Cancel';

        actionCell.append(saveButton, cancelButton);


        // Cancel editing
        cancelButton.addEventListener('click', function() {

            renderProductRow(row, product);

        });


        // Save edited product using AJAX
        saveButton.addEventListener('click', async function() {

            const updatedProduct = {
                name: nameInput.value.trim(),
                quantity: quantityInput.value,
                price: priceInput.value
            };

            if (
                !updatedProduct.name ||
                !quantityInput.checkValidity() ||
                !priceInput.checkValidity()
            ) {

                showMessage('Please enter valid product information.', 'danger');

                return;

            }

            saveButton.disabled = true;

            try {

                const response = await fetch(
                    updateUrl.replace('__ID__', encodeURIComponent(product.id)),
                    {

                        method: 'PUT',

                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },

                        body: JSON.stringify(updatedProduct)

                    }
                );

                const data = await response.json();

                if (!response.ok) {
                    throw data;
                }

                // Update existing row without refreshing
                renderProductRow(row, data.product);

                // Recalculate grand total
                updateGrandTotal();

                showMessage(data.message);

            } catch(error) {

                showMessage(
                    error.errors
                        ? Object.values(error.errors).flat().join(' ')
                        : error.message || 'Unable to update product.',
                    'danger'
                );

                saveButton.disabled = false;

            }

        });

    });


    // Calculate initial grand total
    updateGrandTotal();


</script>
</body>
</html>

