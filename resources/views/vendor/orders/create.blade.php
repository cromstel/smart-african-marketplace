@extends('vendor.layout')

@section('title', 'Create Manual Order')
@section('header', 'Create Manual Order')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('vendor.orders.index') }}" class="text-gray-400 hover:text-white transition" aria-label="Back">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h3 class="text-2xl font-bold text-white">Create Manual Order</h3>
            <p class="text-gray-400 mt-1">Place orders on behalf of customers</p>
        </div>
    </div>

    @if(session('error'))
        <div class="bg-red-500/10 border border-red-500 text-red-500 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-500/10 border border-red-500 rounded-lg p-4">
            <p class="text-red-500 font-semibold mb-2">Please fix the following errors:</p>
            <ul class="list-disc list-inside text-red-400">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('vendor.orders.store') }}" class="space-y-6">
        @csrf

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h4 class="text-lg font-semibold text-white mb-4">Customer Information</h4>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Select Customer *</label>
                <select name="customer_id" id="customer_id" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-orange-500">
                    <option value="">Choose a customer...</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                            {{ $customer->name }} ({{ $customer->email }})
                        </option>
                    @endforeach
                </select>
                <p class="text-gray-400 text-sm mt-1">Select the customer for this order</p>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h4 class="text-lg font-semibold text-white mb-4">Order Items</h4>
            <div id="products-container" class="space-y-4">
                <div class="product-item p-4 bg-gray-700/30 rounded-lg border border-gray-600">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-300 mb-2">Product *</label>
                            <select name="products[0][id]" required class="product-select w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-orange-500">
                                <option value="">Choose a product...</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-stock="{{ $product->stock_quantity }}">
                                        {{ $product->name }} - ${{ number_format($product->price, 2) }} (Stock: {{ $product->stock_quantity }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Quantity *</label>
                            <input type="number" name="products[0][quantity]" min="1" value="1" required class="quantity-input w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-orange-500">
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-between">
                        <p class="text-gray-400 text-sm">Item Total: <span class="item-total text-white font-semibold">$0.00</span></p>
                    </div>
                </div>
            </div>

            <button type="button" id="add-product" class="mt-4 bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Another Product
            </button>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h4 class="text-lg font-semibold text-white mb-4">Order Costs</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Shipping Cost</label>
                    <input type="number" name="shipping_cost" id="shipping_cost" step="0.01" min="0" value="0" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-orange-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Discount</label>
                    <input type="number" name="discount" id="discount" step="0.01" min="0" value="0" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-orange-500">
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h4 class="text-lg font-semibold text-white mb-4">Order Notes</h4>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Internal Notes</label>
                <textarea name="notes" rows="4" placeholder="Add internal notes about this order (not visible to customer)..." class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-orange-500">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h4 class="text-lg font-semibold text-white mb-4">Order Summary</h4>
            <div class="space-y-2">
                <div class="flex justify-between text-gray-300">
                    <span>Subtotal</span>
                    <span id="summary-subtotal" class="font-semibold">$0.00</span>
                </div>
                <div class="flex justify-between text-gray-300">
                    <span>Shipping</span>
                    <span id="summary-shipping" class="font-semibold">$0.00</span>
                </div>
                <div class="flex justify-between text-gray-300">
                    <span>Discount</span>
                    <span id="summary-discount" class="font-semibold">-$0.00</span>
                </div>
                <div class="border-t border-gray-700 pt-2 mt-2 flex justify-between text-white text-lg">
                    <span class="font-bold">Total</span>
                    <span id="summary-total" class="font-bold text-orange-500">$0.00</span>
                </div>
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-8 py-3 rounded-lg transition font-semibold">
                Create Order
            </button>
            <a href="{{ route('vendor.orders.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-8 py-3 rounded-lg transition font-semibold">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
let productIndex = 1;

document.getElementById('add-product').addEventListener('click', function() {
    const container = document.getElementById('products-container');
    const newItem = document.querySelector('.product-item').cloneNode(true);
    
    newItem.querySelectorAll('select, input').forEach(el => {
        el.name = el.name.replace('[0]', `[${productIndex}]`);
        if (el.tagName === 'SELECT') {
            el.value = '';
        } else if (el.type === 'number') {
            el.value = '1';
        }
    });
    
    newItem.querySelector('.item-total').textContent = '$0.00';
    
    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'text-red-500 hover:text-red-400 text-sm';
    removeBtn.textContent = 'Remove';
    removeBtn.onclick = function() {
        newItem.remove();
        calculateTotals();
    };
    newItem.querySelector('.mt-3').appendChild(removeBtn);
    
    container.appendChild(newItem);
    attachProductListeners(newItem);
    productIndex++;
});

function attachProductListeners(item) {
    const select = item.querySelector('.product-select');
    const quantityInput = item.querySelector('.quantity-input');
    const totalSpan = item.querySelector('.item-total');
    
    function updateItemTotal() {
        const selectedOption = select.options[select.selectedIndex];
        const price = parseFloat(selectedOption.dataset.price || 0);
        const quantity = parseInt(quantityInput.value || 0);
        const total = price * quantity;
        totalSpan.textContent = `$${total.toFixed(2)}`;
        calculateTotals();
    }
    
    select.addEventListener('change', updateItemTotal);
    quantityInput.addEventListener('input', updateItemTotal);
}

function calculateTotals() {
    let subtotal = 0;
    
    document.querySelectorAll('.product-item').forEach(item => {
        const select = item.querySelector('.product-select');
        const quantityInput = item.querySelector('.quantity-input');
        const selectedOption = select.options[select.selectedIndex];
        const price = parseFloat(selectedOption.dataset.price || 0);
        const quantity = parseInt(quantityInput.value || 0);
        subtotal += price * quantity;
    });
    
    const shipping = parseFloat(document.getElementById('shipping_cost').value || 0);
    const discount = parseFloat(document.getElementById('discount').value || 0);
    const total = subtotal + shipping - discount;
    
    document.getElementById('summary-subtotal').textContent = `$${subtotal.toFixed(2)}`;
    document.getElementById('summary-shipping').textContent = `$${shipping.toFixed(2)}`;
    document.getElementById('summary-discount').textContent = `-$${discount.toFixed(2)}`;
    document.getElementById('summary-total').textContent = `$${Math.max(0, total).toFixed(2)}`;
}

document.querySelectorAll('.product-item').forEach(item => attachProductListeners(item));
document.getElementById('shipping_cost').addEventListener('input', calculateTotals);
document.getElementById('discount').addEventListener('input', calculateTotals);
</script>
@endsection

