package com.africanmarket.app.data.model

import com.google.gson.annotations.SerializedName

data class User(
    @SerializedName("id") val id: Int,
    @SerializedName("name") val name: String? = null,
    @SerializedName("firstname") val firstname: String? = null,
    @SerializedName("lastname") val lastname: String? = null,
    @SerializedName("email") val email: String? = null,
    @SerializedName("role") val role: String? = null,
    @SerializedName("phone") val phone: String? = null,
    @SerializedName("avatar") val avatar: String? = null,
    @SerializedName("is_active") val isActive: Boolean? = null,
    @SerializedName("email_verified_at") val emailVerifiedAt: String? = null,
    @SerializedName("loyalty_points_balance") val loyaltyPointsBalance: Int? = null,
    @SerializedName("referral_code") val referralCode: String? = null,
    @SerializedName("created_at") val createdAt: String? = null,
)

data class AuthResponse(
    @SerializedName("message") val message: String? = null,
    @SerializedName("token") val token: String? = null,
    @SerializedName("token_type") val tokenType: String? = null,
    @SerializedName("user") val user: User? = null,
)

data class Vendor(
    @SerializedName("id") val id: Int,
    @SerializedName("name") val name: String,
)

data class ProductImage(
    @SerializedName("id") val id: Int? = null,
    @SerializedName("image") val image: String? = null,
    @SerializedName("sort_order") val sortOrder: Int? = null,
)

data class ProductVariant(
    @SerializedName("id") val id: Int? = null,
    @SerializedName("name") val name: String? = null,
    @SerializedName("price") val price: String? = null,
    @SerializedName("attributes") val attributes: Map<String, Any>? = null,
)

data class Category(
    @SerializedName("id") val id: Int,
    @SerializedName("name") val name: String,
    @SerializedName("slug") val slug: String? = null,
    @SerializedName("description") val description: String? = null,
    @SerializedName("image") val image: String? = null,
    @SerializedName("banner_image") val bannerImage: String? = null,
    @SerializedName("parent_id") val parentId: Int? = null,
    @SerializedName("type") val type: String? = null,
    @SerializedName("sort_order") val sortOrder: Int? = null,
    @SerializedName("is_active") val isActive: Boolean? = null,
    @SerializedName("products_count") val productsCount: Int? = null,
)

data class Brand(
    @SerializedName("id") val id: Int,
    @SerializedName("name") val name: String,
    @SerializedName("slug") val slug: String? = null,
    @SerializedName("description") val description: String? = null,
    @SerializedName("logo") val logo: String? = null,
    @SerializedName("website") val website: String? = null,
    @SerializedName("is_active") val isActive: Boolean? = null,
    @SerializedName("sort_order") val sortOrder: Int? = null,
    @SerializedName("products_count") val productsCount: Int? = null,
    @SerializedName("vendor") val vendor: Vendor? = null,
)

data class Product(
    @SerializedName("id") val id: Int,
    @SerializedName("name") val name: String,
    @SerializedName("slug") val slug: String? = null,
    @SerializedName("sku") val sku: String? = null,
    @SerializedName("description") val description: String? = null,
    @SerializedName("short_description") val shortDescription: String? = null,
    @SerializedName("price") val price: String? = null,
    @SerializedName("compare_price") val comparePrice: String? = null,
    @SerializedName("stock_quantity") val stockQuantity: Int? = null,
    @SerializedName("stock_status") val stockStatus: String? = null,
    @SerializedName("weight") val weight: String? = null,
    @SerializedName("origin_country") val originCountry: String? = null,
    @SerializedName("attributes") val attributes: Map<String, Any>? = null,
    @SerializedName("featured_image") val featuredImage: String? = null,
    @SerializedName("is_featured") val isFeatured: Boolean? = null,
    @SerializedName("is_active") val isActive: Boolean? = null,
    @SerializedName("average_rating") val averageRating: Float? = null,
    @SerializedName("reviews_count") val reviewsCount: Int? = null,
    @SerializedName("sales_count") val salesCount: Int? = null,
    @SerializedName("views_count") val viewsCount: Int? = null,
    @SerializedName("category") val category: Category? = null,
    @SerializedName("brand") val brand: Brand? = null,
    @SerializedName("images") val images: List<ProductImage>? = null,
    @SerializedName("variants") val variants: List<ProductVariant>? = null,
)

data class CartItem(
    @SerializedName("id") val id: Int,
    @SerializedName("product_id") val productId: Int,
    @SerializedName("product_variant_id") val productVariantId: Int? = null,
    @SerializedName("quantity") val quantity: Int,
    @SerializedName("price") val price: String? = null,
    @SerializedName("subtotal") val subtotal: String? = null,
    @SerializedName("product") val product: Product? = null,
    @SerializedName("variant") val variant: ProductVariant? = null,
)

data class Cart(
    @SerializedName("id") val id: Int? = null,
    @SerializedName("subtotal") val subtotal: String? = null,
    @SerializedName("tax") val tax: String? = null,
    @SerializedName("shipping") val shipping: String? = null,
    @SerializedName("discount") val discount: String? = null,
    @SerializedName("total") val total: String? = null,
    @SerializedName("coupon_code") val couponCode: String? = null,
    @SerializedName("items") val items: List<CartItem>? = null,
    @SerializedName("item_count") val itemCount: Int? = null,
)

data class Address(
    @SerializedName("id") val id: Int? = null,
    @SerializedName("type") val type: String? = null,
    @SerializedName("first_name") val firstName: String? = null,
    @SerializedName("last_name") val lastName: String? = null,
    @SerializedName("company") val company: String? = null,
    @SerializedName("address_line1") val addressLine1: String? = null,
    @SerializedName("address_line2") val addressLine2: String? = null,
    @SerializedName("city") val city: String? = null,
    @SerializedName("state") val state: String? = null,
    @SerializedName("postal_code") val postalCode: String? = null,
    @SerializedName("country") val country: String? = null,
    @SerializedName("phone") val phone: String? = null,
    @SerializedName("is_default") val isDefault: Boolean? = null,
    @SerializedName("full_name") val fullName: String? = null,
    @SerializedName("full_address") val fullAddress: String? = null,
)

data class OrderItem(
    @SerializedName("id") val id: Int,
    @SerializedName("product_id") val productId: Int? = null,
    @SerializedName("product_variant_id") val productVariantId: Int? = null,
    @SerializedName("vendor_id") val vendorId: Int? = null,
    @SerializedName("product_name") val productName: String? = null,
    @SerializedName("product_sku") val productSku: String? = null,
    @SerializedName("product_attributes") val productAttributes: String? = null,
    @SerializedName("quantity") val quantity: Int? = null,
    @SerializedName("price") val price: String? = null,
    @SerializedName("subtotal") val subtotal: String? = null,
    @SerializedName("tax") val tax: String? = null,
    @SerializedName("total") val total: String? = null,
    @SerializedName("product") val product: Product? = null,
)

data class Payment(
    @SerializedName("id") val id: Int? = null,
    @SerializedName("payment_method") val paymentMethod: String? = null,
    @SerializedName("amount") val amount: String? = null,
    @SerializedName("currency") val currency: String? = null,
    @SerializedName("status") val status: String? = null,
    @SerializedName("authorization_url") val authorizationUrl: String? = null,
    @SerializedName("access_code") val accessCode: String? = null,
    @SerializedName("reference") val reference: String? = null,
    @SerializedName("provider_reference") val providerReference: String? = null,
    @SerializedName("transaction_id") val transactionId: String? = null,
)

data class Order(
    @SerializedName("id") val id: Int,
    @SerializedName("order_number") val orderNumber: String? = null,
    @SerializedName("is_parent") val isParent: Boolean? = null,
    @SerializedName("subtotal") val subtotal: String? = null,
    @SerializedName("tax") val tax: String? = null,
    @SerializedName("shipping_cost") val shippingCost: String? = null,
    @SerializedName("discount") val discount: String? = null,
    @SerializedName("total") val total: String? = null,
    @SerializedName("coupon_code") val couponCode: String? = null,
    @SerializedName("payment_status") val paymentStatus: String? = null,
    @SerializedName("status") val status: String? = null,
    @SerializedName("payment_method") val paymentMethod: String? = null,
    @SerializedName("tracking_number") val trackingNumber: String? = null,
    @SerializedName("customer_notes") val customerNotes: String? = null,
    @SerializedName("paid_at") val paidAt: String? = null,
    @SerializedName("shipped_at") val shippedAt: String? = null,
    @SerializedName("delivered_at") val deliveredAt: String? = null,
    @SerializedName("cancelled_at") val cancelledAt: String? = null,
    @SerializedName("created_at") val createdAt: String? = null,
    @SerializedName("items") val items: List<OrderItem>? = null,
    @SerializedName("billing_address") val billingAddress: Address? = null,
    @SerializedName("shipping_address") val shippingAddress: Address? = null,
    @SerializedName("payment") val payment: Payment? = null,
)

data class Meta(
    @SerializedName("current_page") val currentPage: Int? = null,
    @SerializedName("from") val from: Int? = null,
    @SerializedName("last_page") val lastPage: Int? = null,
    @SerializedName("per_page") val perPage: Int? = null,
    @SerializedName("to") val to: Int? = null,
    @SerializedName("total") val total: Int? = null,
)

data class Links(
    @SerializedName("first") val first: String? = null,
    @SerializedName("last") val last: String? = null,
    @SerializedName("prev") val prev: String? = null,
    @SerializedName("next") val next: String? = null,
)

data class PaginatedResponse<T>(
    @SerializedName("success") val success: Boolean? = null,
    @SerializedName("data") val data: List<T>? = null,
    @SerializedName("message") val message: String? = null,
    @SerializedName("meta") val meta: Meta? = null,
    @SerializedName("links") val links: Links? = null,
)

data class ApiResponse<T>(
    @SerializedName("success") val success: Boolean? = null,
    @SerializedName("data") val data: T? = null,
    @SerializedName("message") val message: String? = null,
)

data class RemoteVersion(
    @SerializedName("versionCode") val versionCode: Int? = null,
    @SerializedName("versionName") val versionName: String? = null,
    @SerializedName("apkUrl") val apkUrl: String? = null,
    @SerializedName("required") val required: Boolean? = null,
    @SerializedName("message") val message: String? = null,
)

data class LoginRequest(
    @SerializedName("email") val email: String,
    @SerializedName("password") val password: String,
    @SerializedName("device_name") val deviceName: String = "android",
)

data class RegisterRequest(
    @SerializedName("name") val name: String,
    @SerializedName("email") val email: String,
    @SerializedName("password") val password: String,
    @SerializedName("password_confirmation") val passwordConfirmation: String,
    @SerializedName("device_name") val deviceName: String = "android",
)

data class UpdateProfileRequest(
    @SerializedName("firstname") val firstname: String? = null,
    @SerializedName("lastname") val lastname: String? = null,
    @SerializedName("email") val email: String? = null,
    @SerializedName("phone") val phone: String? = null,
)

data class AddToCartRequest(
    @SerializedName("product_id") val productId: Int,
    @SerializedName("quantity") val quantity: Int = 1,
    @SerializedName("product_variant_id") val productVariantId: Int? = null,
)

data class AddressRequest(
    @SerializedName("type") val type: String? = null,
    @SerializedName("first_name") val firstName: String,
    @SerializedName("last_name") val lastName: String,
    @SerializedName("company") val company: String? = null,
    @SerializedName("address_line1") val addressLine1: String,
    @SerializedName("address_line2") val addressLine2: String? = null,
    @SerializedName("city") val city: String,
    @SerializedName("state") val state: String? = null,
    @SerializedName("postal_code") val postalCode: String? = null,
    @SerializedName("country") val country: String,
    @SerializedName("phone") val phone: String? = null,
    @SerializedName("is_default") val isDefault: Boolean? = null,
)

data class ShippingPreviewRequest(
    @SerializedName("address_id") val addressId: Int,
)

data class CheckoutRequest(
    @SerializedName("billing_address_id") val billingAddressId: Int,
    @SerializedName("shipping_address_id") val shippingAddressId: Int,
    @SerializedName("payment_method") val paymentMethod: String,
    @SerializedName("customer_notes") val customerNotes: String? = null,
    @SerializedName("phone_number") val phoneNumber: String? = null,
    @SerializedName("mobile_money_provider") val mobileMoneyProvider: String? = null,
    @SerializedName("stripe_token") val stripeToken: String? = null,
)

data class ShippingPreview(
    @SerializedName("subtotal") val subtotal: String? = null,
    @SerializedName("tax") val tax: String? = null,
    @SerializedName("shipping") val shipping: String? = null,
    @SerializedName("discount") val discount: String? = null,
    @SerializedName("total") val total: String? = null,
    @SerializedName("mode") val mode: String? = null,
)

data class CheckoutResponse(
    @SerializedName("order") val order: Order,
    @SerializedName("payment") val payment: Payment? = null,
)
