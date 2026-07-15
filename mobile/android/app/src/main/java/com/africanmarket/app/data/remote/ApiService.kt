package com.africanmarket.app.data.remote

import com.africanmarket.app.data.model.*
import retrofit2.Call
import retrofit2.http.*

interface ApiService {

    @POST("api/v1/mobile/auth/login")
    fun login(@Body body: LoginRequest): Call<AuthResponse>

    @POST("api/v1/mobile/auth/register")
    fun register(@Body body: RegisterRequest): Call<AuthResponse>

    @GET("api/v1/mobile/auth/me")
    fun me(): Call<ApiResponse<User>>

    @PUT("api/v1/mobile/auth/profile")
    fun updateProfile(@Body body: UpdateProfileRequest): Call<ApiResponse<User>>

    @POST("api/v1/mobile/auth/logout")
    fun logout(): Call<ApiResponse<Any>>

    @GET("api/v1/products")
    fun products(
        @Query("category") category: Int? = null,
        @Query("search") search: String? = null,
        @Query("min_price") minPrice: Double? = null,
        @Query("max_price") maxPrice: Double? = null,
        @Query("sort") sort: String? = null,
        @Query("per_page") perPage: Int = 15,
        @Query("page") page: Int = 1,
    ): Call<PaginatedResponse<Product>>

    @GET("api/v1/products/{id}")
    fun product(@Path("id") id: Int): Call<ApiResponse<Product>>

    @GET("api/v1/categories")
    fun categories(@Query("parent_id") parentId: Int? = null): Call<ApiResponse<List<Category>>>

    @GET("api/v1/brands")
    fun brands(
        @Query("search") search: String? = null,
        @Query("per_page") perPage: Int = 15,
        @Query("page") page: Int = 1,
    ): Call<PaginatedResponse<Brand>>

    @GET("api/v1/cart")
    fun getCart(): Call<ApiResponse<Cart>>

    @POST("api/v1/cart")
    fun addToCart(@Body body: AddToCartRequest): Call<ApiResponse<Cart>>

    @GET("api/v1/orders")
    fun orders(@Query("page") page: Int = 1): Call<PaginatedResponse<Order>>

    @GET("api/v1/orders/{id}")
    fun order(@Path("id") id: Int): Call<ApiResponse<Order>>

    @GET("api/v1/addresses")
    fun addresses(): Call<ApiResponse<List<Address>>>

    @POST("api/v1/addresses")
    fun createAddress(@Body body: AddressRequest): Call<ApiResponse<Address>>

    @PUT("api/v1/addresses/{id}")
    fun updateAddress(@Path("id") id: Int, @Body body: AddressRequest): Call<ApiResponse<Address>>

    @DELETE("api/v1/addresses/{id}")
    fun deleteAddress(@Path("id") id: Int): Call<ApiResponse<Any>>

    @POST("api/v1/checkout/shipping-preview")
    fun shippingPreview(@Body body: ShippingPreviewRequest): Call<ApiResponse<ShippingPreview>>

    @POST("api/v1/checkout")
    fun checkout(@Body body: CheckoutRequest): Call<ApiResponse<CheckoutResponse>>

    @GET("releases/android.json")
    fun remoteVersion(): Call<RemoteVersion>
}

fun apiService(): ApiService = RetrofitClient.instance.create(ApiService::class.java)
