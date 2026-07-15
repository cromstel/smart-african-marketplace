package com.africanmarket.app.data.repository

import com.africanmarket.app.data.model.*
import com.africanmarket.app.data.remote.apiService
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import retrofit2.HttpException

sealed class ApiResult<out T> {
    data class Success<T>(val data: T) : ApiResult<T>()
    data class Error(val message: String, val code: Int? = null) : ApiResult<Nothing>()
}

inline fun <T> safeCall(block: () -> T): ApiResult<T> = try {
    ApiResult.Success(block())
} catch (e: HttpException) {
    val body = runCatching { e.response()?.errorBody()?.string() }.getOrNull()
    ApiResult.Error(body ?: e.message(), e.code())
} catch (e: Exception) {
    ApiResult.Error(e.message ?: "Network error")
}

suspend fun <T> io(block: () -> ApiResult<T>): ApiResult<T> = withContext(Dispatchers.IO) { block() }

private fun <T> ApiResult<T>.orThrow(): T = when (this) {
    is ApiResult.Success -> data
    is ApiResult.Error -> throw IllegalStateException(message)
}

object AuthRepository {
    suspend fun login(email: String, password: String) = io {
        safeCall { apiService().login(LoginRequest(email, password)).execute().body().orThrowIfNull("login") }
    }

    suspend fun register(name: String, email: String, password: String, passwordConfirmation: String) = io {
        safeCall {
            apiService().register(RegisterRequest(name, email, password, passwordConfirmation)).execute().body()
                .orThrowIfNull("register")
        }
    }

    suspend fun me() = io { safeCall { apiService().me().execute().body()?.data.orThrowIfNull("me") } }
    suspend fun updateProfile(req: UpdateProfileRequest) =
        io { safeCall { apiService().updateProfile(req).execute().body()?.data.orThrowIfNull("profile") } }

    suspend fun logout() = io { safeCall { apiService().logout().execute().body() } }
}

object ProductRepository {
    suspend fun products(page: Int, search: String?, category: Int?, sort: String?) = io {
        safeCall { apiService().products(category, search, null, null, sort, 15, page).execute().body().orThrowIfNull("products") }
    }

    suspend fun product(id: Int) = io { safeCall { apiService().product(id).execute().body()?.data.orThrowIfNull("product") } }
    suspend fun categories(parentId: Int?) = io { safeCall { apiService().categories(parentId).execute().body()?.data ?: emptyList() } }
}

object CartRepository {
    suspend fun getCart() = io { safeCall { apiService().getCart().execute().body()?.data.orThrowIfNull("cart") } }
    suspend fun addToCart(productId: Int, quantity: Int, variantId: Int?) = io {
        safeCall { apiService().addToCart(AddToCartRequest(productId, quantity, variantId)).execute().body()?.data.orThrowIfNull("cart") }
    }
}

object OrderRepository {
    suspend fun orders(page: Int) = io { safeCall { apiService().orders(page).execute().body().orThrowIfNull("orders") } }
    suspend fun order(id: Int) = io { safeCall { apiService().order(id).execute().body()?.data.orThrowIfNull("order") } }
}

object AddressRepository {
    suspend fun list() = io { safeCall { apiService().addresses().execute().body()?.data ?: emptyList() } }
    suspend fun create(req: AddressRequest) = io { safeCall { apiService().createAddress(req).execute().body()?.data.orThrowIfNull("address") } }
    suspend fun update(id: Int, req: AddressRequest) = io { safeCall { apiService().updateAddress(id, req).execute().body()?.data.orThrowIfNull("address") } }
    suspend fun delete(id: Int) = io { safeCall { apiService().deleteAddress(id).execute().body() } }
}

object CheckoutRepository {
    suspend fun shippingPreview(addressId: Int) = io {
        safeCall { apiService().shippingPreview(ShippingPreviewRequest(addressId)).execute().body()?.data.orThrowIfNull("preview") }
    }

    suspend fun checkout(req: CheckoutRequest) = io {
        safeCall { apiService().checkout(req).execute().body()?.data.orThrowIfNull("checkout") }
    }
}

object VersionRepository {
    suspend fun remoteVersion() = io { safeCall { apiService().remoteVersion().execute().body().orThrowIfNull("version") } }
}

private fun <T> T?.orThrowIfNull(what: String): T = this ?: throw IllegalStateException("Empty response: $what")
