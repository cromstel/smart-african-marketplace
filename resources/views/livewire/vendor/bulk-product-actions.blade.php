<div x-data="{ showDiscountInputs: @entangle('bulkAction').live === 'apply_discount' }">
    <div class="mb-6 space-y-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex-1">
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="Search products..." 
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
            </div>
            <select wire:model.live="statusFilter" 
                    class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                <option value="all">All Products</option>
                <option value="active">Active Only</option>
                <option value="inactive">Inactive Only</option>
            </select>
        </div>

        @if(count($selectedProducts) > 0)
            <div class="bg-orange-900/20 border border-orange-600 rounded-lg p-4">
                <div class="flex flex-col md:flex-row md:items-center gap-4">
                    <div class="flex items-center text-orange-400">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-medium">{{ count($selectedProducts) }} product(s) selected</span>
                    </div>

                    <div class="flex flex-1 flex-wrap gap-3">
                        <select wire:model.live="bulkAction" 
                                class="flex-1 min-w-[200px] px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                            <option value="">Select Action...</option>
                            <option value="activate">Activate</option>
                            <option value="deactivate">Deactivate</option>
                            <option value="feature">Mark as Featured</option>
                            <option value="unfeature">Remove from Featured</option>
                            <option value="in_stock">Mark In Stock</option>
                            <option value="out_of_stock">Mark Out of Stock</option>
                            <option value="apply_discount">Apply Discount</option>
                            <option value="remove_discount">Remove Discount</option>
                            <option value="delete">Delete</option>
                        </select>

                        @if($bulkAction === 'apply_discount')
                            <div class="flex gap-2">
                                <select wire:model="discountType" 
                                        class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                    <option value="percentage">Percentage (%)</option>
                                    <option value="fixed">Fixed Amount ($)</option>
                                </select>
                                <input type="number" 
                                       wire:model="discountValue" 
                                       min="0" 
                                       step="0.01" 
                                       placeholder="Value" 
                                       class="w-32 px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                            </div>
                        @endif

                        <button type="button" 
                                wire:click="applyBulkAction" 
                                wire:loading.attr="disabled"
                                @if($bulkAction === 'delete') onclick="return confirm('Are you sure you want to delete the selected products? This action cannot be undone.')" @endif
                                class="px-6 py-2 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="applyBulkAction">Apply</span>
                            <span wire:loading wire:target="applyBulkAction">Processing...</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="bg-gray-800 rounded-lg shadow overflow-hidden">
        @if($products->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input type="checkbox" 
                                       wire:model.live="selectAll" 
                                       class="w-4 h-4 text-orange-600 bg-gray-700 border-gray-600 rounded focus:ring-orange-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">SKU</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach($products as $product)
                            <tr class="hover:bg-gray-700 transition {{ in_array($product->id, $selectedProducts) ? 'bg-orange-900/10' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" 
                                           wire:model.live="selectedProducts" 
                                           value="{{ $product->id }}" 
                                           class="w-4 h-4 text-orange-600 bg-gray-700 border-gray-600 rounded focus:ring-orange-500">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if($product->featured_image)
                                            <img src="{{ '/storage/' . ltrim($product->featured_image ?? '', '/') }}" 
                                                 alt="{{ $product->name }}" 
                                                 class="h-10 w-10 rounded object-cover mr-3">
                                        @else
                                            <div class="h-10 w-10 rounded bg-gray-700 flex items-center justify-center mr-3">
                                                <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                        <div class="max-w-xs">
                                            <div class="text-sm font-medium text-white truncate">{{ $product->name }}</div>
                                            @if($product->is_featured)
                                                <span class="text-xs text-yellow-400">⭐ Featured</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-300 font-mono">{{ $product->sku }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-300">{{ $product->category->name ?? 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-white font-semibold">${{ number_format($product->price, 2) }}</div>
                                    @if($product->compare_price)
                                        <div class="text-xs text-gray-400 line-through">${{ number_format($product->compare_price, 2) }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-white">{{ $product->stock_quantity }}</div>
                                    @if($product->isLowStock())
                                        <span class="text-xs text-yellow-400">Low Stock</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($product->is_active)
                                        <span class="px-2 py-1 text-xs rounded bg-green-900 text-green-200">Active</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded bg-gray-700 text-gray-400">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('vendor.products.edit', $product) }}" class="text-blue-400 hover:text-blue-300" title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('vendor.products.variants.index', $product) }}" class="text-purple-400 hover:text-purple-300" title="Manage Variants">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 bg-gray-900">
                {{ $products->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <h3 class="text-lg font-medium text-white mb-2">No products found</h3>
                <p class="text-gray-400">{{ $search ? 'Try adjusting your search criteria' : 'Get started by adding your first product' }}</p>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('notify', (event) => {
                const data = event[0];
                const bgColor = data.type === 'success' ? 'bg-green-600' : 'bg-red-600';
                
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity duration-300`;
                notification.textContent = data.message;
                
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.style.opacity = '0';
                    setTimeout(() => notification.remove(), 300);
                }, 3000);
            });
        });
    </script>
</div>

