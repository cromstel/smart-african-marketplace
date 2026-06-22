<?php

namespace App\Livewire\Vendor;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class BulkProductActions extends Component
{
    use WithPagination;

    public $selectedProducts = [];

    public $selectAll = false;

    public $bulkAction = '';

    public $discountType = 'percentage';

    public $discountValue = 0;

    public $search = '';

    public $statusFilter = 'all';

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedProducts = $this->getProducts()->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selectedProducts = [];
        }
    }

    public function updatedSelectedProducts()
    {
        $this->selectAll = false;
    }

    public function applyBulkAction()
    {
        if (empty($this->selectedProducts)) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Please select at least one product.',
            ]);

            return;
        }

        if (empty($this->bulkAction)) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Please select an action.',
            ]);

            return;
        }

        DB::beginTransaction();

        try {
            $products = Product::whereIn('id', $this->selectedProducts)
                ->where('vendor_id', auth()->user()->vendor->id)
                ->get();

            $count = $products->count();

            switch ($this->bulkAction) {
                case 'activate':
                    $products->each->update(['is_active' => true]);
                    $message = "{$count} product(s) activated successfully.";
                    break;

                case 'deactivate':
                    $products->each->update(['is_active' => false]);
                    $message = "{$count} product(s) deactivated successfully.";
                    break;

                case 'feature':
                    $products->each->update(['is_featured' => true]);
                    $message = "{$count} product(s) marked as featured.";
                    break;

                case 'unfeature':
                    $products->each->update(['is_featured' => false]);
                    $message = "{$count} product(s) removed from featured.";
                    break;

                case 'in_stock':
                    $products->each->update(['stock_status' => 'in_stock']);
                    $message = "{$count} product(s) marked as in stock.";
                    break;

                case 'out_of_stock':
                    $products->each->update(['stock_status' => 'out_of_stock']);
                    $message = "{$count} product(s) marked as out of stock.";
                    break;

                case 'apply_discount':
                    if ($this->discountValue <= 0) {
                        throw new \Exception('Discount value must be greater than 0');
                    }

                    foreach ($products as $product) {
                        if ($this->discountType === 'percentage') {
                            $discountAmount = ($product->price * $this->discountValue) / 100;
                            $newPrice = max(0, $product->price - $discountAmount);
                        } else {
                            $newPrice = max(0, $product->price - $this->discountValue);
                        }

                        $product->update([
                            'compare_price' => $product->compare_price ?: $product->price,
                            'price' => $newPrice,
                        ]);
                    }

                    $message = "Discount applied to {$count} product(s) successfully.";
                    break;

                case 'remove_discount':
                    foreach ($products as $product) {
                        if ($product->compare_price) {
                            $product->update([
                                'price' => $product->compare_price,
                                'compare_price' => null,
                            ]);
                        }
                    }
                    $message = "Discount removed from {$count} product(s).";
                    break;

                case 'delete':
                    $products->each->delete();
                    $message = "{$count} product(s) deleted successfully.";
                    break;

                default:
                    throw new \Exception('Invalid bulk action');
            }

            DB::commit();

            $this->selectedProducts = [];
            $this->selectAll = false;
            $this->bulkAction = '';
            $this->discountValue = 0;

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'An error occurred: '.$e->getMessage(),
            ]);
        }
    }

    public function getProducts()
    {
        $query = Product::where('vendor_id', auth()->user()->vendor->id);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%");
            });
        }

        if ($this->statusFilter !== 'all') {
            $query->where('is_active', $this->statusFilter === 'active');
        }

        return $query->latest();
    }

    public function render()
    {
        $products = $this->getProducts()->paginate(20);

        return view('livewire.vendor.bulk-product-actions', [
            'products' => $products,
        ]);
    }
}
